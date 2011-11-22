<?php

/**
 * Test: Nette\Config\Config::merge() with NeonAdapter
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../bootstrap.php';



$obj = (object) NULL;
$arr1 = array('a' => 'b', 'x');
$arr2 = array('c' => 'd', 'y');


function merge($left, $right)
{
	file_put_contents(TEMP_DIR . '/left.neon', $left);
	file_put_contents(TEMP_DIR . '/right.neon', $right);

	$config = new Config;
	return $config->merge($config->load(TEMP_DIR . '/left.neon'), $config->load(TEMP_DIR . '/right.neon'));
}


// replace
Assert::same( array('item' => array()), merge('item!:', 'item:') );

Assert::same( array('item' => array()), merge('item!:', 'item: 123') );

Assert::same( array('item' => array()), merge('item!: []', 'item: []') );

Assert::throws(function() {
	merge('item!: 231', 'item:');
}, 'Nette\InvalidStateException');

Assert::throws(function() {
	merge('item!: 231', 'item: 231');
}, 'Nette\InvalidStateException');


// inherit
Assert::same( array(
	'parent' => 1,
	'child' => array(Config::EXTENDS_KEY => 'parent')
), merge('child < parent:', 'parent: 1') );
