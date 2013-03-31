<?php

/**
 * Test: Nette\Config\Configurator and services inheritance and overwriting.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



class MyApp extends Nette\Application\Application
{
}



$configurator = new Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.inheritance2.neon')
	->createContainer();


Assert::true( $container->{'nette.application'} instanceof MyApp );
Assert::null( $container->{'nette.application'}->catchExceptions );
Assert::same( 'Error', $container->{'nette.application'}->errorPresenter );

Assert::true( $container->app2 instanceof MyApp );
Assert::null( $container->app2->catchExceptions );
Assert::null( $container->app2->errorPresenter );
