<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\DI\Extensions;

use Nette,
	Nette\DI\ContainerBuilder,
	Nette\Utils\Validators;



/**
 * Core Nette Framework services.
 *
 * @author     David Grudl
 */
class NetteExtension extends Nette\DI\CompilerExtension
{
	public $defaults = array(
		'session' => array(
			'debugger' => FALSE,
			'iAmUsingBadHost' => NULL,
			'autoStart' => 'smart',  // true|false|smart
			'expiration' => NULL,
		),
		'application' => array(
			'debugger' => TRUE,
			'errorPresenter' => 'Nette:Error',
			'catchExceptions' => '%productionMode%',
			'mapping' => NULL
		),
		'routing' => array(
			'debugger' => TRUE,
			'routes' => array(), // of [mask => action]
		),
		'security' => array(
			'debugger' => TRUE,
			'frames' => 'SAMEORIGIN', // X-Frame-Options
			'users' => array(), // of [user => password]
			'roles' => array(), // of [role => parents]
			'resources' => array(), // of [resource => parents]
		),
		'mailer' => array(
			'smtp' => FALSE,
		),
		'database' => array(), // of [name => dsn, user, password, debugger, explain, autowired, reflection]
		'forms' => array(
			'messages' => array(),
		),
		'latte' => array(
			'xhtml' => TRUE,
			'macros' => array(),
		),
		'container' => array(
			'debugger' => FALSE,
			'accessors' => TRUE,
		),
		'debugger' => array(
			'email' => NULL,
			'editor' => NULL,
			'browser' => NULL,
			'strictMode' => NULL,
			'bar' => array(), // of class name
			'blueScreen' => array(), // of callback
		),
	);

	public $databaseDefaults = array(
		'dsn' => NULL,
		'user' => NULL,
		'password' => NULL,
		'options' => NULL,
		'debugger' => TRUE,
		'explain' => TRUE,
		'reflection' => 'Nette\Database\Reflection\DiscoveredReflection',
	);



	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if (isset($config['xhtml'])) {
			$config['latte']['xhtml'] = $config['xhtml'];
		}
		$container->addDefinition('nette')->setClass('Nette\DI\Extensions\NetteAccessor', array('@container'));

		$this->setupCache($container);
		$this->setupHttp($container);
		$this->setupSession($container, $config['session']);
		$this->setupSecurity($container, $config['security']);
		$this->setupApplication($container, $config['application']);
		$this->setupRouting($container, $config['routing']);
		$this->setupMailer($container, $config['mailer']);
		$this->setupForms($container);
		$this->setupTemplating($container, $config['latte']);
		$this->setupDatabase($container, $config['database']);
		$this->setupContainer($container, $config['container']);
	}



	private function setupCache(ContainerBuilder $container)
	{
		$container->addDefinition($this->prefix('cacheJournal'))
			->setClass('Nette\Caching\Storages\FileJournal', array($container->expand('%tempDir%')));

		$container->addDefinition('cacheStorage') // no namespace for back compatibility
			->setClass('Nette\Caching\Storages\FileStorage', array($container->expand('%tempDir%/cache')));

		$container->addDefinition($this->prefix('templateCacheStorage'))
			->setClass('Nette\Caching\Storages\PhpFileStorage', array($container->expand('%tempDir%/cache')))
			->setAutowired(FALSE);

		$container->addDefinition($this->prefix('cache'))
			->setClass('Nette\Caching\Cache', array(1 => $container::literal('$namespace')))
			->setParameters(array('namespace' => NULL));
	}



	private function setupHttp(ContainerBuilder $container)
	{
		$container->addDefinition($this->prefix('httpRequestFactory'))
			->setClass('Nette\Http\RequestFactory')
			->addSetup('setEncoding', array('UTF-8'));

		$container->addDefinition('httpRequest') // no namespace for back compatibility
			->setClass('Nette\Http\Request')
			->setFactory('@Nette\Http\RequestFactory::createHttpRequest');

		$container->addDefinition('httpResponse') // no namespace for back compatibility
			->setClass('Nette\Http\Response');

		$container->addDefinition($this->prefix('httpContext'))
			->setClass('Nette\Http\Context');
	}



	private function setupSession(ContainerBuilder $container, array $config)
	{
		$session = $container->addDefinition('session') // no namespace for back compatibility
			->setClass('Nette\Http\Session');

		if (isset($config['expiration'])) {
			$session->addSetup('setExpiration', array($config['expiration']));
		}
		if (isset($config['iAmUsingBadHost'])) {
			$session->addSetup('Nette\Framework::$iAmUsingBadHost = ?;', array((bool) $config['iAmUsingBadHost']));
		}

		if ($container->parameters['debugMode'] && $config['debugger']) {
			$session->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array(
				new Nette\DI\Statement('Nette\Http\Diagnostics\SessionPanel')
			));
		}

		unset($config['expiration'], $config['autoStart'], $config['iAmUsingBadHost'], $config['debugger']);
		if (!empty($config)) {
			$session->addSetup('setOptions', array($config));
		}
	}



	private function setupSecurity(ContainerBuilder $container, array $config)
	{
		$container->addDefinition($this->prefix('userStorage'))
			->setClass('Nette\Http\UserStorage');

		$user = $container->addDefinition('user') // no namespace for back compatibility
			->setClass('Nette\Security\User');

		if ($container->parameters['debugMode'] && $config['debugger']) {
			$user->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array(
				new Nette\DI\Statement('Nette\Security\Diagnostics\UserPanel')
			));
		}

		if ($config['users']) {
			$container->addDefinition($this->prefix('authenticator'))
				->setClass('Nette\Security\SimpleAuthenticator', array($config['users']));
		}

		if ($config['roles'] || $config['resources']) {
			$authorizator = $container->addDefinition($this->prefix('authorizator'))
				->setClass('Nette\Security\Permission');
			foreach ($config['roles'] as $role => $parents) {
				$authorizator->addSetup('addRole', array($role, $parents));
			}
			foreach ($config['resources'] as $resource => $parents) {
				$authorizator->addSetup('addResource', array($resource, $parents));
			}
		}
	}



	private function setupApplication(ContainerBuilder $container, array $config)
	{
		$application = $container->addDefinition('application') // no namespace for back compatibility
			->setClass('Nette\Application\Application')
			->addSetup('$catchExceptions', $config['catchExceptions'])
			->addSetup('$errorPresenter', $config['errorPresenter'])
			->addSetup('!headers_sent() && header(?);', 'X-Powered-By: Nette Framework');

		if ($config['debugger']) {
			$application->addSetup('Nette\Application\Diagnostics\RoutingPanel::initializePanel');
		}

		$presenterFactory = $container->addDefinition($this->prefix('presenterFactory'))
			->setClass('Nette\Application\PresenterFactory', array(
				isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL
			));
		if ($config['mapping']) {
			$presenterFactory->addSetup('setMapping', array($config['mapping']));
		}
	}



	private function setupRouting(ContainerBuilder $container, array $config)
	{
		$router = $container->addDefinition('router') // no namespace for back compatibility
			->setClass('Nette\Application\Routers\RouteList');

		foreach ($config['routes'] as $mask => $action) {
			$router->addSetup('$service[] = new Nette\Application\Routers\Route(?, ?);', array($mask, $action));
		}

		if ($container->parameters['debugMode'] && $config['debugger']) {
			$container->getDefinition('application')->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array(
				new Nette\DI\Statement('Nette\Application\Diagnostics\RoutingPanel')
			));
		}
	}



	private function setupMailer(ContainerBuilder $container, array $config)
	{
		if (empty($config['smtp'])) {
			$container->addDefinition($this->prefix('mailer'))
				->setClass('Nette\Mail\SendmailMailer');
		} else {
			$container->addDefinition($this->prefix('mailer'))
				->setClass('Nette\Mail\SmtpMailer', array($config));
		}

		$container->addDefinition($this->prefix('mail'))
			->setClass('Nette\Mail\Message')
			->addSetup('setMailer')
			->setShared(FALSE);
	}



	private function setupForms(ContainerBuilder $container)
	{
		$container->addDefinition($this->prefix('basicForm'))
			->setClass('Nette\Forms\Form')
			->setShared(FALSE);
	}



	private function setupTemplating(ContainerBuilder $container, array $config)
	{
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

		foreach ($config['macros'] as $macro) {
			if (strpos($macro, '::') === FALSE && class_exists($macro)) {
				$macro .= '::install';

			} else {
				Validators::assert($macro, 'callable');
			}

			$latte->addSetup($macro . '(?->compiler)', array('@self'));
		}
	}



	private function setupDatabase(ContainerBuilder $container, array $config)
	{
		if (isset($config['dsn'])) {
			$config = array('default' => $config);
		}

		$autowired = TRUE;
		foreach ((array) $config as $name => $info) {
			if (!is_array($info)) {
				continue;
			}
			$info += $this->databaseDefaults + array('autowired' => $autowired);
			$autowired = FALSE;

			foreach ((array) $info['options'] as $key => $value) {
				if (preg_match('#^PDO::\w+\z#', $key)) {
					unset($info['options'][$key]);
					$info['options'][constant($key)] = $value;
				}
			}

			if (!$info['reflection']) {
				$reflection = NULL;
			} elseif (is_string($info['reflection'])) {
				$reflection = new Nette\DI\Statement(preg_match('#^[a-z]+\z#', $info['reflection'])
					? 'Nette\Database\Reflection\\' . ucfirst($info['reflection']) . 'Reflection'
					: $info['reflection'], strtolower($info['reflection']) === 'discovered' ? array('@self') : array());
			} else {
				$tmp = Nette\DI\Compiler::filterArguments(array($info['reflection']));
				$reflection = reset($tmp);
			}

			$connection = $container->addDefinition($this->prefix("database.$name"))
				->setClass('Nette\Database\Connection', array($info['dsn'], $info['user'], $info['password'], $info['options']))
				->setAutowired($info['autowired'])
				->addSetup('setSelectionFactory', array(
					new Nette\DI\Statement('Nette\Database\Table\SelectionFactory', array('@self', $reflection)),
				))
				->addSetup('Nette\Diagnostics\Debugger::$blueScreen->addPanel(?)', array(
					'Nette\Database\Diagnostics\ConnectionPanel::renderException'
				));

			if ($container->parameters['debugMode'] && $info['debugger']) {
				$connection->addSetup('Nette\Database\Helpers::createDebugPanel', array($connection, !empty($info['explain']), $name));
			}
		}
	}



	private function setupContainer(ContainerBuilder $container, array $config)
	{
		if ($config['accessors']) {
			$container->parameters['nette']['accessors'] = TRUE;
		}
	}



	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$initialize = $class->methods['initialize'];
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		// debugger
		foreach (array('email', 'editor', 'browser', 'strictMode', 'maxLen', 'maxDepth', 'showLocation', 'scream') as $key) {
			if (isset($config['debugger'][$key])) {
				$initialize->addBody('Nette\Diagnostics\Debugger::$? = ?;', array($key, $config['debugger'][$key]));
			}
		}

		if ($container->parameters['debugMode']) {
			if ($config['container']['debugger']) {
				$config['debugger']['bar'][] = 'Nette\DI\Diagnostics\ContainerPanel';
			}

			foreach ((array) $config['debugger']['bar'] as $item) {
				$initialize->addBody($container->formatPhp(
					'Nette\Diagnostics\Debugger::$bar->addPanel(?);',
					Nette\DI\Compiler::filterArguments(array(is_string($item) ? new Nette\DI\Statement($item) : $item))
				));
			}

			foreach ((array) $config['debugger']['blueScreen'] as $item) {
				$initialize->addBody($container->formatPhp(
					'Nette\Diagnostics\Debugger::$blueScreen->addPanel(?);',
					Nette\DI\Compiler::filterArguments(array($item))
				));
			}
		}

		if (!empty($container->parameters['tempDir'])) {
			$initialize->addBody('Nette\Caching\Storages\FileStorage::$useDirectories = ?;', array($this->checkTempDir($container->expand('%tempDir%/cache'))));
		}

		foreach ((array) $config['forms']['messages'] as $name => $text) {
			$initialize->addBody('Nette\Forms\Rules::$defaultMessages[Nette\Forms\Form::?] = ?;', array($name, $text));
		}

		if ($config['session']['autoStart'] === 'smart') {
			$initialize->addBody('$this->getService("session")->exists() && $this->getService("session")->start();');
		} elseif ($config['session']['autoStart']) {
			$initialize->addBody('$this->getService("session")->start();');
		}

		if (empty($config['latte']['xhtml'])) {
			$initialize->addBody('Nette\Utils\Html::$xhtml = ?;', array((bool) $config['latte']['xhtml']));
		}

		if (isset($config['security']['frames']) && $config['security']['frames'] !== TRUE) {
			$frames = $config['security']['frames'];
			if ($frames === FALSE) {
				$frames = 'DENY';
			} elseif (preg_match('#^https?:#', $frames)) {
				$frames = "ALLOW-FROM $frames";
			}
			$initialize->addBody('header(?);', array("X-Frame-Options: $frames"));
		}

		foreach ($container->findByTag('run') as $name => $on) {
			if ($on) {
				$initialize->addBody('$this->getService(?);', array($name));
			}
		}

		if (!empty($config['container']['accessors'])) {
			$definitions = $container->definitions;
			ksort($definitions);
			foreach ($definitions as $name => $def) {
				if ($def->shared && Nette\PhpGenerator\Helpers::isIdentifier($name)) {
					$type = $def->implement ?: $def->class;
					$class->addDocument("@property $type \$$name");
				}
			}
		}
	}



	private function checkTempDir($dir)
	{
		// checks whether directory is writable
		$uniq = uniqid('_', TRUE);
		if (!@mkdir("$dir/$uniq")) { // @ - is escalated to exception
			throw new Nette\InvalidStateException("Unable to write to directory '$dir'. Make this directory writable.");
		}

		// checks whether subdirectory is writable
		$isWritable = @file_put_contents("$dir/$uniq/_", '') !== FALSE; // @ - error is expected
		if ($isWritable) {
			unlink("$dir/$uniq/_");
		}
		rmdir("$dir/$uniq");
		return $isWritable;
	}

}
