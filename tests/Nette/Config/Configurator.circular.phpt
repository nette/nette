<?php

/**
 * Test: Nette\DI\Configurator and circular references in parameters.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI\Configurator;



require __DIR__ . '/../bootstrap.php';



$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);

Assert::exception(function() use ($configurator) {
	$configurator->addConfig('files/config.circular.ini', 'production')
		->createContainer();
}, 'Nette\InvalidArgumentException', 'Circular reference detected for variables: foo, foobar, bar.');
