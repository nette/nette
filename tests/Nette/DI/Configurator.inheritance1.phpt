<?php

/**
 * Test: Nette\Config\Configurator and services inheritance and overwriting.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



class MyApp extends Nette\Application\Application
{
}



$configurator = new Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/configurator.inheritance1.neon')
	->createContainer();


Assert::true( $container->getService('application') instanceof MyApp );
Assert::true( $container->getService('application')->catchExceptions );
Assert::same( 'Error', $container->getService('application')->errorPresenter );

Assert::true( $container->getService('app2') instanceof MyApp );
Assert::true( $container->getService('app2')->catchExceptions );
Assert::same( 'Error', $container->getService('app2')->errorPresenter );
