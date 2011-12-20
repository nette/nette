<?php

/**
 * Test: Nette\Utils\Validators::assertField()
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\Validators;



require __DIR__ . '/../bootstrap.php';


$arr = array('first' => TRUE, 'second' => TRUE);

Assert::throws(function() use ($arr) {
	Validators::assertFields(NULL, 'foo', 'foo');
}, 'Nette\Utils\AssertionException', "The first argument expects to be array, NULL given.");

Validators::assertFields($arr, 'bool');

$arr2 = array('first' => 1, 'second' => TRUE);

Assert::throws(function() use ($arr2) {
	Validators::assertFields($arr2, 'int');
}, 'Nette\Utils\AssertionException', "The item 'second' in array expects to be int, boolean given.");
