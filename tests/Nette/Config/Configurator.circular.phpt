<?php

/**
 * Test: Nette\Config\Configurator and circular references in parameters.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);

Assert::exception(function() use ($configurator) {
	$configurator->addConfig('files/config.circular.ini', 'production')
		->createContainer();
}, 'Nette\InvalidArgumentException', 'Circular reference detected for variables: foo, foobar, bar.');
