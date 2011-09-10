<?php

/**
 * Test: Nette\Utils\Arrays::getRef()
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\Arrays;



require __DIR__ . '/../bootstrap.php';



$arr  = array(
	NULL => 'first',
	1 => 'second',
	7 => array(
		'item' => 'third',
	),
);

// Single item

$dolly = $arr;
$item = & Arrays::getRef($dolly, NULL);
$item = 'changed';
Assert::same( array(
	'' => 'changed',
	1 => 'second',
	7 => array(
		'item' => 'third',
	),
), $dolly );


$dolly = $arr;
$item = & Arrays::getRef($dolly, 'undefined');
$item = 'changed';
Assert::same( array(
	'' => 'first',
	1 => 'second',
	7 => array(
		'item' => 'third',
	),
	'undefined' => 'changed',
), $dolly );



// Traversing

$dolly = $arr;
$item = & Arrays::getRef($dolly, array());
$item = 'changed';
Assert::same( 'changed', $dolly );


$dolly = $arr;
$item = & Arrays::getRef($dolly, array(7, 'item'));
$item = 'changed';
Assert::same( array(
	'' => 'first',
	1 => 'second',
	7 => array(
		'item' => 'changed',
	),
), $dolly );



// Error

Assert::throws(function() use ($arr) {
	$dolly = $arr;
	$item = & Arrays::getRef($dolly, array(7, 'item', 3));
}, 'InvalidArgumentException', 'Traversed item is not an array.');
