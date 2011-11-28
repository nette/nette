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



$configurator1 = Nette\Environment::getConfigurator();

Assert::true( $configurator1 instanceof Configurator );

Assert::throws(function() {
	$configurator2 = new Configurator;
}, 'Nette\InvalidStateException', 'Nette\Config\Configurator has already been created automatically by Nette\Environment at %a%:%d%');
