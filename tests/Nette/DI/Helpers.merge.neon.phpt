<?php

/**
 * Test: Nette\DI\Config\Helpers::merge() with NeonAdapter
 *
 * @author     David Grudl
 * @package    Nette\DI\Config
 */

use Nette\DI\Config,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$obj = new stdClass;
$arr1 = array('a' => 'b', 'x');
$arr2 = array('c' => 'd', 'y');


function merge($left, $right)
{
	file_put_contents(TEMP_DIR . '/left.neon', $left);
	file_put_contents(TEMP_DIR . '/right.neon', $right);

	$config = new Config\Loader;
	return Config\Helpers::merge($config->load(TEMP_DIR . '/left.neon'), $config->load(TEMP_DIR . '/right.neon'));
}


// replace
Assert::same( array('item' => array()), merge('item!:', 'item:') );

Assert::same( array('item' => array()), merge('item!:', 'item: 123') );

Assert::same( array('item' => array()), merge('item!: []', 'item: []') );

Assert::exception(function() {
	merge('item!: 231', 'item:');
}, 'Nette\InvalidStateException');

Assert::exception(function() {
	merge('item!: 231', 'item: 231');
}, 'Nette\InvalidStateException');


// inherit
Assert::same( array(
	'parent' => 1,
	'child' => array(Config\Helpers::EXTENDS_KEY => 'parent')
), merge('child < parent:', 'parent: 1') );
