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



$configurator = new Configurator;
$configurator->setProductionMode(TRUE);
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.inheritance4.neon', Configurator::NONE)
	->createContainer();


Assert::true( $container->application instanceof Nette\Application\Application );
