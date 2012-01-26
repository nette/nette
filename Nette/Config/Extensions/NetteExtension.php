<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Config\Extensions;

use Nette,
	Nette\DI\ContainerBuilder,
	Nette\Utils\Validators;



/**
 * Core Nette Framework services.
 *
 * @author     David Grudl
 */
class NetteExtension extends Nette\Config\CompilerExtension
{
	public $defaults = array(
		'xhtml' => TRUE,
		'session' => array(
			'iAmUsingBadHost' => NULL,
			'autoStart' => NULL,  // true|false|smart
			'expiration' => NULL,
		),
		'application' => array(
			'debugger' => TRUE,
			'errorPresenter' => NULL,
			'catchExceptions' => '%productionMode%',
		),
		'routing' => array(
			'debugger' => TRUE,
			'routes' => array(), // of [mask => action]
		),
		'security' => array(
			'debugger' => TRUE,
			'frames' => 'DENY', // X-Frame-Options
			'users' => array(), // of [user => password]
			'roles' => array(), // of [role => parents]
			'resources' => array(), // of [resource => parents]
		),
		'mailer' => array(
			'smtp' => FALSE,
		),
		'database' => array(), // of [name => dsn, user, password, debugger, explain, autowired]
		'forms' => array(
			'messages' => array(),
		),
		'container' => array(
			'debugger' => FALSE,
		),
	);

	public $databaseDefaults = array(
		'dsn' => NULL,
		'user' => NULL,
		'password' => NULL,
		'options' => NULL,
		'debugger' => TRUE,
		'explain' => TRUE,
	);



	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);


		// cache
		$container->addDefinition('cacheJournal')
			->setClass('Nette\Caching\Storages\FileJournal', array('%tempDir%'));

		$container->addDefinition('cacheStorage')
			->setClass('Nette\Caching\Storages\FileStorage', array('%tempDir%/cache'));

		$container->addDefinition('templateCacheStorage')
			->setClass('Nette\Caching\Storages\PhpFileStorage', array('%tempDir%/cache'))
			->setAutowired(FALSE);

		$container->addDefinition($this->prefix('cache'))
			->setClass('Nette\Caching\Cache', array(1 => '%namespace%'))
			->setParameters(array('namespace' => NULL));


		// http
		$container->addDefinition($this->prefix('httpRequestFactory'))
			->setClass('Nette\Http\RequestFactory')
			->addSetup('setEncoding', array('UTF-8'))
			->setInternal(TRUE);

		$container->addDefinition('httpRequest')
			->setClass('Nette\Http\Request')
			->setFactory('@Nette\Http\RequestFactory::createHttpRequest');

		$container->addDefinition('httpResponse')
			->setClass('Nette\Http\Response');

		$container->addDefinition('httpContext')
			->setClass('Nette\Http\Context');


		// session
		$session = $container->addDefinition('session')
			->setClass('Nette\Http\Session');

		if (isset($config['session']['expiration'])) {
			$session->addSetup('setExpiration', array($config['session']['expiration']));
		}
		if (isset($config['session']['iAmUsingBadHost'])) {
			$session->addSetup('Nette\Framework::$iAmUsingBadHost = ?;', array((bool) $config['session']['iAmUsingBadHost']));
		}
		unset($config['session']['expiration'], $config['session']['autoStart'], $config['session']['iAmUsingBadHost']);
		if (!empty($config['session'])) {
			Validators::assertField($config, 'session', 'array');
			$session->addSetup('setOptions', array($config['session']));
		}


		// security
		$container->addDefinition($this->prefix('userStorage'))
			->setClass('Nette\Http\UserStorage');

		$user = $container->addDefinition('user')
			->setClass('Nette\Security\User');

		if (!$container->parameters['productionMode'] && $config['security']['debugger']) {
			$user->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array(
				new Nette\DI\Statement('Nette\Security\Diagnostics\UserPanel')
			));
		}

		if ($config['security']['users']) {
			$container->addDefinition($this->prefix('authenticator'))
				->setClass('Nette\Security\SimpleAuthenticator', array($config['security']['users']));
		}

		if ($config['security']['roles'] || $config['security']['resources']) {
			$authorizator = $container->addDefinition($this->prefix('authorizator'))
				->setClass('Nette\Security\Permission');
			foreach ($config['security']['roles'] as $role => $parents) {
				$authorizator->addSetup('addRole', array($role, $parents));
			}
			foreach ($config['security']['resources'] as $resource => $parents) {
				$authorizator->addSetup('addResource', array($resource, $parents));
			}
		}


		// application
		$application = $container->addDefinition('application')
			->setClass('Nette\Application\Application')
			->addSetup('$catchExceptions', $config['application']['catchExceptions'])
			->addSetup('$errorPresenter', $config['application']['errorPresenter']);

		if ($config['application']['debugger']) {
			$application->addSetup('Nette\Application\Diagnostics\RoutingPanel::initializePanel');
		}

		$container->addDefinition('presenterFactory')
			->setClass('Nette\Application\PresenterFactory', array(
				isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL
			));


		// routing
		$router = $container->addDefinition('router')
			->setClass('Nette\Application\Routers\RouteList');

		foreach ($config['routing']['routes'] as $mask => $action) {
			$router->addSetup('$service[] = new Nette\Application\Routers\Route(?, ?);', array($mask, $action));
		}

		if (!$container->parameters['productionMode'] && $config['routing']['debugger']) {
			$application->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array(
				new Nette\DI\Statement('Nette\Application\Diagnostics\RoutingPanel')
			));
		}


		// mailer
		if (empty($config['mailer']['smtp'])) {
			$container->addDefinition('mailer')
				->setClass('Nette\Mail\SendmailMailer');
		} else {
			Validators::assertField($config, 'mailer', 'array');
			$container->addDefinition('mailer')
				->setClass('Nette\Mail\SmtpMailer', array($config['mailer']));
		}

		$container->addDefinition($this->prefix('mail'))
			->setClass('Nette\Mail\Message')
			->addSetup('setMailer')
			->setShared(FALSE);


		// forms
		$container->addDefinition($this->prefix('basicForm'))
			->setClass('Nette\Forms\Form')
			->setShared(FALSE);


		// templating
		$latte = $container->addDefinition($this->prefix('latte'))
			->setClass('Nette\Latte\Engine')
			->setShared(FALSE);

		if (empty($config['xhtml'])) {
			$latte->addSetup('$service->getCompiler()->defaultContentType = ?', Nette\Latte\Compiler::CONTENT_HTML);
		}

		$container->addDefinition($this->prefix('template'))
			->setClass('Nette\Templating\FileTemplate')
			->addSetup('registerFilter', array($latte))
			->addSetup('registerHelperLoader', array('Nette\Templating\Helpers::loader'))
			->setShared(FALSE);


		// database
		$container->addDefinition($this->prefix('database'))
				->setClass('Nette\DI\NestedAccessor', array('@container', $this->prefix('database')));

		$autowired = TRUE;
		foreach ((array) $config['database'] as $name => $info) {
			if (!is_array($info)) {
				continue;
			}
			$info += $this->databaseDefaults + array('autowired' => $autowired);
			$autowired = FALSE;

			foreach ((array) $info['options'] as $key => $value) {
				unset($info['options'][$key]);
				$info['options'][constant($key)] = $value;
			}

			$connection = $container->addDefinition($this->prefix("database_$name"))
				->setClass('Nette\Database\Connection', array($info['dsn'], $info['user'], $info['password'], $info['options']))
				->setAutowired($info['autowired'])
				->addSetup('setCacheStorage')
				->addSetup('setDatabaseReflection', array(new Nette\DI\Statement('Nette\Database\Reflection\DiscoveredReflection')))
				->addSetup('Nette\Diagnostics\Debugger::$blueScreen->addPanel(?)', array(
					'Nette\Database\Diagnostics\ConnectionPanel::renderException'
				));

			if (!$container->parameters['productionMode'] && $info['debugger']) {
				$panel = $container->addDefinition($this->prefix("database_{$name}ConnectionPanel"))
					->setClass('Nette\Database\Diagnostics\ConnectionPanel')
					->setAutowired(FALSE)
					->addSetup('$explain', !empty($info['explain']))
					->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array('@self'));

				$connection->addSetup('$service->onQuery[] = ?', array(array($panel, 'logQuery')));
			}
		}
	}



	public function afterCompile(Nette\Utils\PhpGenerator\ClassType $class)
	{
		$initialize = $class->methods['initialize'];
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if (!empty($container->parameters['tempDir'])) {
			$initialize->addBody($this->checkTempDir($container->expand('%tempDir%/cache')));
		}

		foreach ((array) $config['forms']['messages'] as $name => $text) {
			$initialize->addBody('Nette\Forms\Rules::$defaultMessages[Nette\Forms\Form::?] = ?;', array($name, $text));
		}

		if ($config['session']['autoStart'] === 'smart') {
			$initialize->addBody('$this->session->exists() && $this->session->start();');
		} elseif ($config['session']['autoStart']) {
			$initialize->addBody('$this->session->start();');
		}

		if (empty($config['xhtml'])) {
			$initialize->addBody('Nette\Utils\Html::$xhtml = ?;', array((bool) $config['xhtml']));
		}

		if (isset($config['security']['frames'])) {
			$initialize->addBody('header(?);', array('X-Frame-Options: ' . $config['security']['frames']));
		}

		if (!$container->parameters['productionMode'] && $config['container']['debugger']) {
			$initialize->addBody('Nette\Diagnostics\Debugger::$bar->addPanel(new Nette\DI\Diagnostics\ContainerPanel($this));');
		}

		foreach ($container->findByTag('run') as $name => $on) {
			if ($on) {
				$initialize->addBody('$this->getService(?);', array($name));
			}
		}
	}



	private function checkTempDir($dir)
	{
		// checks whether directory is writable
		$uniq = uniqid('_', TRUE);
		if (!@mkdir("$dir/$uniq", 0777)) { // @ - is escalated to exception
			throw new Nette\InvalidStateException("Unable to write to directory '$dir'. Make this directory writable.");
		}

		// tests subdirectory mode
		$useDirs = @file_put_contents("$dir/$uniq/_", '') !== FALSE; // @ - error is expected
		@unlink("$dir/$uniq/_");
		@rmdir("$dir/$uniq"); // @ - directory may not already exist

		return 'Nette\Caching\Storages\FileStorage::$useDirectories = ' . ($useDirs ? 'TRUE' : 'FALSE') . ";\n";
	}

}
