<?php

/**
 * Test: Nette\Config\Configurator and minimal container.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



$configurator = new Configurator;
$configurator->setCacheDirectory(TEMP_DIR);
$configurator->addParameters(array(
	'hello' => 'world',
));
$container = $configurator->getContainer();

Assert::true( $container instanceof SystemContainer );

Assert::same( array(
	'hello' => 'world',
	'appDir' => __DIR__,
	'wwwDir' => NULL,
	'productionMode' => TRUE,
	'consoleMode' => PHP_SAPI === 'cli',
	'tempDir' => TEMP_DIR,
), $container->parameters );

Assert::true( $container->cacheJournal instanceof Nette\Caching\Storages\FileJournal );
Assert::true( $container->cacheStorage instanceof Nette\Caching\Storages\FileStorage );
Assert::true( $container->templateCacheStorage instanceof Nette\Caching\Storages\PhpFileStorage );
Assert::true( $container->httpRequest instanceof Nette\Http\Request );
Assert::true( $container->httpResponse instanceof Nette\Http\Response );
Assert::true( $container->httpContext instanceof Nette\Http\Context );
Assert::true( $container->session instanceof Nette\Http\Session );
Assert::true( $container->user instanceof Nette\Http\User );
Assert::true( $container->application instanceof Nette\Application\Application );
Assert::true( $container->router instanceof Nette\Application\Routers\RouteList );
Assert::true( $container->presenterFactory instanceof Nette\Application\PresenterFactory );
Assert::true( $container->mailer instanceof Nette\Mail\SendmailMailer );

			