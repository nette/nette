<?php

/**
 * Test: Nette\DI\Compiler and circular references in parameters.
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function() {
	$loader = new DI\Config\Loader;
	$compiler = new DI\Compiler;
	$compiler->compile($loader->load('files/compiler.parameters.circular.ini'), 'Container', 'Nette\DI\Container');
}, 'Nette\InvalidArgumentException', 'Circular reference detected for variables: foo, foobar, bar.');
