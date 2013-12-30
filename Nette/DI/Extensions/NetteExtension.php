<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
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
		'http' => array(
			'proxy' => array(),
		),
		'session' => array(
			'debugger' => FALSE,
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
			'host' => NULL,
			'port' => NULL,
			'username' => NULL,
			'password' => NULL,
			'secure' => NULL,
			'timeout' => NULL,
		),
		'database' => array(), // of [name => dsn, user, password, debugger, explain, autowired, reflection]
		'forms' => array(
			'messages' => array(),
		),
		'latte' => array(
			'xhtml' => FALSE,
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
			'maxLen' => NULL,
			'maxDepth' => NULL,
			'showLocation' => NULL,
			'scream' => NULL,
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
		'autowired' => NULL,
	);


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if (isset($config['xhtml'])) {
			$config['latte']['xhtml'] = $config['xhtml'];
			unset($config['xhtml']);
		}
		$container->addDefinition('nette')->setClass('Nette\DI\Extensions\NetteAccessor', array('@container'));

		$this->validate($config, $this->defaults, 'nette');

		$this->setupCache($container);
		$this->setupHttp($container, $config['http']);
		$this->setupSession($container, $config['session']);
		$this->setupSecurity($container, $config['security']);
		$this->setupApplication($container, $config['application']);
		$this->setupRouting($container, $config['routing']);
		$this->setupMailer($container, $config['mailer']);
		$this->setupForms($container);
		$this->setupLatte($container, $config['latte']);
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
			->addSetup('::trigger_error', array('Service cache is deprecated.', E_USER_DEPRECATED))
			->setParameters(array('namespace' => NULL));
	}


	private function setupHttp(ContainerBuilder $container, array $config)
	{
		$this->validate($config, $this->defaults['http'], 'nette.http');

		$container->addDefinition($this->prefix('httpRequestFactory'))
			->setClass('Nette\Http\RequestFactory')
			->addSetup('setProxy', array($config['proxy']));

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

		if ($container->parameters['debugMode'] && $config['debugger']) {
			$session->addSetup('Nette\Diagnostics\Debugger::getBar()->addPanel(?)', array(
				new Nette\DI\Statement('Nette\Http\Diagnostics\SessionPanel')
			));
		}

		unset($config['expiration'], $config['autoStart'], $config['debugger']);
		if (!empty($config)) {
			$session->addSetup('setOptions', array($config));
		}
	}


	private function setupSecurity(ContainerBuilder $container, array $config)
	{
		$this->validate($config, $this->defaults['security'], 'nette.security');

		$container->addDefinition($this->prefix('userStorage'))
			->setClass('Nette\Http\UserStorage');

		$user = $container->addDefinition('user') // no namespace for back compatibility
			->setClass('Nette\Security\User');

		if ($container->parameters['debugMode'] && $config['debugger']) {
			$user->addSetup('Nette\Diagnostics\Debugger::getBar()->addPanel(?)', array(
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
		$this->validate($config, $this->defaults['application'], 'nette.application');

		$application = $container->addDefinition('application') // no namespace for back compatibility
			->setClass('Nette\Application\Application')
			->addSetup('$catchExceptions', array($config['catchExceptions']))
			->addSetup('$errorPresenter', array($config['errorPresenter']));

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
		$this->validate($config, $this->defaults['routing'], 'nette.routing');

		$router = $container->addDefinition('router') // no namespace for back compatibility
			->setClass('Nette\Application\Routers\RouteList');

		foreach ($config['routes'] as $mask => $action) {
			$router->addSetup('$service[] = new Nette\Application\Routers\Route(?, ?);', array($mask, $action));
		}

		if ($container->parameters['debugMode'] && $config['debugger']) {
			$container->getDefinition('application')->addSetup('Nette\Diagnostics\Debugger::getBar()->addPanel(?)', array(
				new Nette\DI\Statement('Nette\Application\Diagnostics\RoutingPanel')
			));
		}
	}


	private function setupMailer(ContainerBuilder $container, array $config)
	{
		$this->validate($config, $this->defaults['mailer'], 'nette.mailer');

		if (empty($config['smtp'])) {
			$container->addDefinition($this->prefix('mailer'))
				->setClass('Nette\Mail\SendmailMailer');
		} else {
			$container->addDefinition($this->prefix('mailer'))
				->setClass('Nette\Mail\SmtpMailer', array($config));
		}

		$container->addDefinition($this->prefix('mail'))
			->setClass('Nette\Mail\Message')
			->addSetup('::trigger_error', array('Service nette.mail is deprecated.', E_USER_DEPRECATED))
			->addSetup('setMailer')
			->setAutowired(FALSE);
	}


	private function setupForms(ContainerBuilder $container)
	{
		$container->addDefinition($this->prefix('basicForm'))
			->setClass('Nette\Forms\Form')
			->addSetup('::trigger_error', array('Service nette.basicForm is deprecated.', E_USER_DEPRECATED))
			->setAutowired(FALSE);
	}


	private function setupLatte(ContainerBuilder $container, array $config)
	{
		$this->validate($config, $this->defaults['latte'], 'nette.latte');

		$latte = $container->addDefinition($this->prefix('latte'))
			->setClass('Nette\Latte\Engine')
			->setAutowired(FALSE);

		if ($config['xhtml']) {
			$latte->addSetup('$service->getCompiler()->defaultContentType = ?', array(Nette\Latte\Compiler::CONTENT_XHTML));
		}

		$container->addDefinition($this->prefix('template'))
			->setClass('Nette\Templating\FileTemplate')
			->addSetup('registerFilter', array($latte))
			->addSetup('registerHelperLoader', array('Nette\Templating\Helpers::loader'))
			->setAutowired(FALSE);

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
			$this->validate($info, $this->databaseDefaults, 'nette.database');

			$info += array('autowired' => $autowired) + $this->databaseDefaults;
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
				->addSetup('setContext', array(
					new Nette\DI\Statement('Nette\Database\Context', array('@self', $reflection)),
				))
				->addSetup('Nette\Diagnostics\Debugger::getBlueScreen()->addPanel(?)', array(
					'Nette\Database\Diagnostics\ConnectionPanel::renderException'
				));

			$container->addDefinition($this->prefix("database.$name.context"))
				->setClass('Nette\Database\Context')
				->setFactory(array($connection, 'getContext'))
				->setAutowired($info['autowired']);

			if ($container->parameters['debugMode'] && $info['debugger']) {
				$connection->addSetup('Nette\Database\Helpers::createDebugPanel', array($connection, !empty($info['explain']), $name));
			}
		}
	}


	private function setupContainer(ContainerBuilder $container, array $config)
	{
		$this->validate($config, $this->defaults['container'], 'nette.container');

		if ($config['accessors']) {
			$container->parameters['container']['accessors'] = TRUE;
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
					'Nette\Diagnostics\Debugger::getBar()->addPanel(?);',
					Nette\DI\Compiler::filterArguments(array(is_string($item) ? new Nette\DI\Statement($item) : $item))
				));
			}
		}

		foreach ((array) $config['debugger']['blueScreen'] as $item) {
			$initialize->addBody($container->formatPhp(
				'Nette\Diagnostics\Debugger::getBlueScreen()->addPanel(?);',
				Nette\DI\Compiler::filterArguments(array($item))
			));
		}

		if (!empty($container->parameters['tempDir'])) {
			$initialize->addBody('Nette\Caching\Storages\FileStorage::$useDirectories = ?;', array($this->checkTempDir($container->expand('%tempDir%/cache'))));
		}

		foreach ((array) $config['forms']['messages'] as $name => $text) {
			$initialize->addBody('Nette\Forms\Rules::$defaultMessages[Nette\Forms\Form::?] = ?;', array($name, $text));
		}

		if ($config['session']['autoStart'] === 'smart') {
			$initialize->addBody('$this->getByType("Nette\Http\Session")->exists() && $this->getByType("Nette\Http\Session")->start();');
		} elseif ($config['session']['autoStart']) {
			$initialize->addBody('$this->getByType("Nette\Http\Session")->start();');
		}

		if ($config['latte']['xhtml']) {
			$initialize->addBody('Nette\Utils\Html::$xhtml = ?;', array(TRUE));
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
				if (Nette\PhpGenerator\Helpers::isIdentifier($name)) {
					$type = $def->implement ?: $def->class;
					$class->addDocument("@property $type \$$name");
				}
			}
		}

		$initialize->addBody("@header('X-Powered-By: Nette Framework');");
		$initialize->addBody("@header('Content-Type: text/html; charset=utf-8');");
		$initialize->addBody('Nette\Utils\SafeStream::register();');
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


	private function validate(array $config, array $expected, $name)
	{
		if ($extra = array_diff_key($config, $expected)) {
			$extra = implode(", $name.", array_keys($extra));
			throw new Nette\InvalidStateException("Unknown option $name.$extra.");
		}
	}

}
