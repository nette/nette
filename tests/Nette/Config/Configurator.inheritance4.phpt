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
$configurator->setCacheDirectory(TEMP_DIR);
$container = $configurator->loadConfig('files/config.inheritance4.neon', FALSE);


Assert::true( $container->application instanceof Nette\Application\Application );
