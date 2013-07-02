<?php

/**
 * Test: Nette\Config\Configurator and minimal container.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config\Configurator;


require __DIR__ . '/../bootstrap.php';


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$configurator->addParameters(array(
	'hello' => 'world',
));
$container = $configurator->createContainer();

Assert::type( 'SystemContainer', $container );

Assert::same( array(
	'appDir' => __DIR__,
	'wwwDir' => NULL,
	'debugMode' => FALSE,
	'productionMode' => TRUE,
	'environment' => 'production',
	'consoleMode' => PHP_SAPI === 'cli',
	'container' => array(
		'class' => 'SystemContainer',
		'parent' => 'Nette\\DI\\Container',
	),
	'tempDir' => TEMP_DIR,
	'hello' => 'world',
), $container->parameters );

Assert::type( 'Nette\Caching\Storages\FileJournal', $container->getService('nette.cacheJournal') );
Assert::type( 'Nette\Caching\Storages\FileStorage', $container->getService('cacheStorage') );
Assert::type( 'Nette\Caching\Storages\PhpFileStorage', $container->getService('nette.templateCacheStorage') );
Assert::type( 'Nette\Http\Request', $container->getService('httpRequest') );
Assert::type( 'Nette\Http\Response', $container->getService('httpResponse') );
Assert::type( 'Nette\Http\Context', $container->getService('nette.httpContext') );
Assert::type( 'Nette\Http\Session', $container->getService('session') );
Assert::type( 'Nette\Security\User', $container->getService('user') );
Assert::type( 'Nette\Http\UserStorage', $container->getService('nette.userStorage') );
Assert::type( 'Nette\Application\Application', $container->getService('application') );
Assert::type( 'Nette\Application\Routers\RouteList', $container->getService('router') );
Assert::type( 'Nette\Application\PresenterFactory', $container->getService('nette.presenterFactory') );
Assert::type( 'Nette\Mail\SendmailMailer', $container->getService('nette.mailer') );

Assert::type( 'Nette\Caching\Cache', $container->nette->createCache() );
Assert::same( 'nm', $container->nette->createCache('nm')->getNamespace() );
Assert::type( 'Nette\Forms\Form', $container->nette->createBasicForm() );
Assert::type( 'Nette\Latte\Engine', $container->nette->createLatte() );
Assert::type( 'Nette\Templating\FileTemplate', $container->nette->createTemplate() );
Assert::type( 'Nette\Mail\Message', $container->nette->createMail() );
