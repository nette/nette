<?php

/**
 * Test: Nette\Config\Configurator and services inheritance and overwriting.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



class MyApp extends Nette\Application\Application
{
}



$configurator = new Configurator;
$configurator->setProductionMode(TRUE);
$configurator->setCacheDirectory(TEMP_DIR);
$container = $configurator->loadConfig('files/config.inheritance1.neon', FALSE);


Assert::true( $container->application instanceof MyApp );
Assert::true( $container->application->catchExceptions );
Assert::same( 'Error', $container->application->errorPresenter );

Assert::true( $container->app2 instanceof MyApp );
Assert::true( $container->app2->catchExceptions );
Assert::same( 'Error', $container->app2->errorPresenter );
