<?php

/**
 * Test: Nette\Config\Configurator::createRobotLoader()
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



$configurator = new Configurator;

Assert::throws(function() use ($configurator) {
	$configurator->setCacheDirectory(NULL)->createRobotLoader();
}, 'Nette\InvalidStateException', "Set path to temporary directory using setCacheDirectory().");


$configurator->setCacheDirectory(TEMP_DIR);
$loader = $configurator->createRobotLoader();

Assert::true( $loader instanceof Nette\Loaders\RobotLoader );
Assert::true( $loader->getCacheStorage() instanceof Nette\Caching\Storages\FileStorage );
