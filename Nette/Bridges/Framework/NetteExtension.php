<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\Framework;

use Nette,
	Nette\DI\ContainerBuilder,
	Nette\Utils\Validators,
	Latte;


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
			'headers' => array(
				'X-Powered-By' => 'Nette Framework',
				'Content-Type' => 'text/html; charset=utf-8',
			),
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
			'users' => array(), // of [user => password] or [user => ['password' => password, 'roles' => [role]]]
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
		'database' => array(), // BC
		'forms' => array(
			'messages' => array(),
		),
		'latte' => array(
			'xhtml' => FALSE,
		),
		'container' => array(
			'debugger' => FALSE,
			'accessors' => FALSE,
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


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if (isset($config['xhtml'])) {
			$config['latte']['xhtml'] = $config['xhtml'];
			unset($config['xhtml']);
		}

		$this->validate($config, $this->defaults, 'nette');

		$this->setupCache($container);
		$this->setupHttp($container, $config['http']);
		$this->setupSession($container, $config['session']);
		$this->setupSecurity($container, $config['security']);
		$this->setupApplication($container, $config['application']);
		$this->setupRouting($container, $config['routing']);
		$this->setupMailer($container, $config['mailer']);
		$this->setupLatte($container, $config['latte']);
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
			->addSetup('::trigger_error', array('Service templateCacheStorage is deprecated.', E_USER_DEPRECATED))
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
			$session->addSetup('Tracy\Debugger::getBar()->addPanel(?)', array(
				new Nette\DI\Statement('Nette\Bridges\HttpTracy\SessionPanel')
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
			$user->addSetup('Tracy\Debugger::getBar()->addPanel(?)', array(
				new Nette\DI\Statement('Nette\Bridges\SecurityTracy\UserPanel')
			));
		}

		if ($config['users']) {
			$usersList = $usersRoles = array();
			foreach ($config['users'] as $username => $data) {
				$usersList[$username] = is_array($data) ? $data['password'] : $data;
				$usersRoles[$username] = is_array($data) && isset($data['roles']) ? $data['roles'] : NULL;
			}

			$container->addDefinition($this->prefix('authenticator'))
				->setClass('Nette\Security\SimpleAuthenticator', array($usersList, $usersRoles));
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
			$application->addSetup('Nette\Bridges\ApplicationTracy\RoutingPanel::initializePanel');
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
			$container->getDefinition('application')->addSetup('Tracy\Debugger::getBar()->addPanel(?)', array(
				new Nette\DI\Statement('Nette\Bridges\ApplicationTracy\RoutingPanel')
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
	}


	private function setupLatte(ContainerBuilder $container, array $config)
	{
		$this->validate($config, $this->defaults['latte'], 'nette.latte');

		$latteFactory = $container->addDefinition($this->prefix('latteFactory'))
			->setClass('Latte\Engine')
			->addSetup('setTempDirectory', array($container->expand('%tempDir%/cache/latte')))
			->addSetup('setAutoRefresh', array($container->parameters['debugMode']))
			->addSetup('setContentType', array($config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML))
			->setImplement('Nette\Bridges\Framework\ILatteFactory');

		$container->addDefinition($this->prefix('templateFactory'))
			->setClass('Nette\Bridges\ApplicationLatte\TemplateFactory');

		$container->addDefinition($this->prefix('latte'))
			->setClass('Latte\Engine')
			->addSetup('::trigger_error', array('Service nette.template is deprecated.', E_USER_DEPRECATED))
			->addSetup('setTempDirectory', array($container->expand('%tempDir%/cache/latte')))
			->addSetup('setAutoRefresh', array($container->parameters['debugMode']))
			->addSetup('setContentType', array($config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML))
			->setAutowired(FALSE);

		$container->addDefinition($this->prefix('template'))
			->setClass('Nette\Templating\FileTemplate')
			->addSetup('::trigger_error', array('Service nette.template is deprecated.', E_USER_DEPRECATED))
			->addSetup('registerFilter', array(new Nette\DI\Statement(array($latteFactory, 'create'))))
			->addSetup('registerHelperLoader', array('Nette\Templating\Helpers::loader'))
			->setAutowired(FALSE);
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
		$initialize->addBody('Nette\Bridges\Framework\TracyBridge::initialize();');

		foreach (array('email', 'editor', 'browser', 'strictMode', 'maxLen', 'maxDepth', 'showLocation', 'scream') as $key) {
			if (isset($config['debugger'][$key])) {
				$initialize->addBody('Tracy\Debugger::$? = ?;', array($key, $config['debugger'][$key]));
			}
		}

		if ($container->parameters['debugMode']) {
			if ($config['container']['debugger']) {
				$config['debugger']['bar'][] = 'Nette\Bridges\DITracy\ContainerPanel';
			}

			foreach ((array) $config['debugger']['bar'] as $item) {
				$initialize->addBody($container->formatPhp(
					'Tracy\Debugger::getBar()->addPanel(?);',
					Nette\DI\Compiler::filterArguments(array(is_string($item) ? new Nette\DI\Statement($item) : $item))
				));
			}
		}

		foreach ((array) $config['debugger']['blueScreen'] as $item) {
			$initialize->addBody($container->formatPhp(
					'Tracy\Debugger::getBlueScreen()->addPanel(?);',
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

		foreach ($config['http']['headers'] as $key => $value) {
			if ($value != NULL) { // intentionally ==
				$initialize->addBody('header(?);', array("$key: $value"));
			}
		}

		$initialize->addBody('Nette\Utils\SafeStream::register();');
		$initialize->addBody('Nette\Reflection\AnnotationsParser::setCacheStorage($this->getByType("Nette\Caching\IStorage"));');
		$initialize->addBody('Nette\Reflection\AnnotationsParser::$autoRefresh = ?;', array($container->parameters['debugMode']));
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
