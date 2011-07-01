<?php

/**
 * Test: Nette\Environment circular references.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../bootstrap.php';



Assert::throws(function() {
	Environment::loadConfig('config.circular.ini', 'production');
}, 'Nette\InvalidArgumentException', 'Circular reference detected for variables: foo, foobar, bar.');
