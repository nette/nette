<?php

/**
 * Test: Nette\Configurator and services inheritance and overwriting.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\Configurator;



require __DIR__ . '/../bootstrap.php';



class MyApp extends Nette\Application\Application
{
}



$configurator = new Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/configurator.inheritance2.neon')
	->createContainer();


Assert::true( $container->getService('application') instanceof MyApp );
Assert::null( $container->getService('application')->catchExceptions );
Assert::same( 'Error', $container->getService('application')->errorPresenter );

Assert::true( $container->getService('app2') instanceof MyApp );
Assert::null( $container->getService('app2')->catchExceptions );
Assert::null( $container->getService('app2')->errorPresenter );
