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


Assert::type( 'MyApp', $container->getService('application') );
Assert::null( $container->getService('application')->catchExceptions );
Assert::same( 'Error', $container->getService('application')->errorPresenter );

Assert::type( 'MyApp', $container->getService('app2') );
Assert::null( $container->getService('app2')->catchExceptions );
Assert::null( $container->getService('app2')->errorPresenter );
