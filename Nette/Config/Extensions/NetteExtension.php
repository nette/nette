<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
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

	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		// cache
		$container->addDefinition('cacheJournal')
			->setClass('Nette\Caching\Storages\FileJournal', array('%cacheDir%'));

		$container->addDefinition('cacheStorage')
			->setClass('Nette\Caching\Storages\FileStorage', array('%cacheDir%'));

		$container->addDefinition('templateCacheStorage')
			->setClass('Nette\Caching\Storages\PhpFileStorage', array('%cacheDir%'))
			->setAutowired(FALSE);

		// http
		$container->addDefinition('httpRequestFactory')
			->setClass('Nette\Http\RequestFactory')
			->addSetup('setEncoding', array('UTF-8'))
			->setInternal(TRUE)
			->setShared(FALSE);

		$container->addDefinition('httpRequest')
			->setClass('Nette\Http\Request')
			->setFactory('@httpRequestFactory::createHttpRequest');

		$container->addDefinition('httpResponse')
			->setClass('Nette\Http\Response');

		$container->addDefinition('httpContext')
			->setClass('Nette\Http\Context');

		$session = $container->addDefinition('session')
			->setClass('Nette\Http\Session');

		if (isset($config['session'])) {
			Validators::assertField($config, 'session', 'array');
			$session->addSetup('setOptions', array($config['session']));
		}
		if (isset($config['session']['expiration'])) {
			$session->addSetup('setExpiration', array($config['session']['expiration']));
		}

		$container->addDefinition('user')
			->setClass('Nette\Http\User');

		// application
		$application = $container->addDefinition('application')
			->setClass('Nette\Application\Application')
			->addSetup('$catchExceptions', '%productionMode%');

		if (empty($config['productionMode'])) {
			$application->addSetup('Nette\Application\Diagnostics\RoutingPanel::initialize'); // enable routing debugger
		}

		$container->addDefinition('router')
			->setClass('Nette\Application\Routers\RouteList');

		$container->addDefinition('presenterFactory')
			->setClass('Nette\Application\PresenterFactory', array(
				isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL
			));

		// mailer
		if (empty($config['mailer']['smtp'])) {
			$container->addDefinition('mailer')
				->setClass('Nette\Mail\SendmailMailer');
		} else {
			Validators::assertField($config, 'mailer', 'array');
			$container->addDefinition('mailer')
				->setClass('Nette\Mail\SmtpMailer', array($config['mailer']));
		}
	}



	public function afterCompile(ContainerBuilder $container, Nette\Utils\PhpGenerator\ClassType $class)
	{
		$initialize = $class->methods['initialize'];

		if (isset($container->parameters['cacheDir'])) {
			$initialize->addBody($this->checkTempDir($container->expand('%cacheDir%')));
		}
		foreach ($container->findByTag('run') as $name => $foo) {
			$initialize->addBody('$this->getService(?);', array($name));
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
