<?php

/**
 * Test: Nette\Configurator::createRobotLoader()
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\Configurator;



require __DIR__ . '/../bootstrap.php';



$configurator = new Configurator;

Assert::exception(function() use ($configurator) {
	$configurator->createRobotLoader();
}, 'Nette\InvalidStateException', "Set path to temporary directory using setTempDirectory().");


$configurator->setTempDirectory(TEMP_DIR);
$loader = $configurator->createRobotLoader();

Assert::true( $loader instanceof Nette\Loaders\RobotLoader );
Assert::true( $loader->getCacheStorage() instanceof Nette\Caching\Storages\FileStorage );
