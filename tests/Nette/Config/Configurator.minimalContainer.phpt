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

Assert::true( $container instanceof SystemContainer );

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

Assert::true( $container->getService('nette.cacheJournal') instanceof Nette\Caching\Storages\FileJournal );
Assert::true( $container->getService('cacheStorage') instanceof Nette\Caching\Storages\FileStorage );
Assert::true( $container->getService('nette.templateCacheStorage') instanceof Nette\Caching\Storages\PhpFileStorage );
Assert::true( $container->getService('httpRequest') instanceof Nette\Http\Request );
Assert::true( $container->getService('httpResponse') instanceof Nette\Http\Response );
Assert::true( $container->getService('nette.httpContext') instanceof Nette\Http\Context );
Assert::true( $container->getService('session') instanceof Nette\Http\Session );
Assert::true( $container->getService('user') instanceof Nette\Security\User );
Assert::true( $container->getService('nette.userStorage') instanceof Nette\Http\UserStorage );
Assert::true( $container->getService('application') instanceof Nette\Application\Application );
Assert::true( $container->getService('router') instanceof Nette\Application\Routers\RouteList );
Assert::true( $container->getService('nette.presenterFactory') instanceof Nette\Application\PresenterFactory );
Assert::true( $container->getService('nette.mailer') instanceof Nette\Mail\SendmailMailer );

Assert::true( $container->nette->createCache() instanceof Nette\Caching\Cache );
Assert::same( 'nm', $container->nette->createCache('nm')->getNamespace() );
Assert::true( $container->nette->createBasicForm() instanceof Nette\Forms\Form );
Assert::true( $container->nette->createLatte() instanceof Nette\Latte\Engine );
Assert::true( $container->nette->createTemplate() instanceof Nette\Templating\FileTemplate );
Assert::true( $container->nette->createMail() instanceof Nette\Mail\Message );
