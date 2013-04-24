<?php

/**
 * Test: Nette\Config\Compiler and circular references in parameters.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config;



require __DIR__ . '/../bootstrap.php';



Assert::exception(function() {
	$loader = new Config\Loader;
	$compiler = new Config\Compiler;
	$compiler->compile($loader->load('files/compiler.parameters.circular.ini'), 'Container', 'Nette\DI\Container');
}, 'Nette\InvalidArgumentException', 'Circular reference detected for variables: foo, foobar, bar.');
