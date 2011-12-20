<?php

/**
 * Test: Nette\Config\Configurator and circular references in parameters.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



$configurator = new Configurator;
$configurator->setCacheDirectory(TEMP_DIR);

Assert::throws(function() use ($configurator) {
	$configurator->addConfig('files/config.circular.ini', 'production')
		->createContainer();
}, 'Nette\InvalidArgumentException', 'Circular reference detected for variables: foo, foobar, bar.');
