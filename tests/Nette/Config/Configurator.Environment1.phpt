<?php

/**
 * Test: Nette\Config\Configurator and coexistence with deprecated Environment.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



$configurator1 = new Configurator;
$configurator2 = new Configurator;

Assert::same( $configurator2, Nette\Environment::getConfigurator() );