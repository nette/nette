<?php

/**
 * Test: TestHelpers::dump() nesting and recursion
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 */

require __DIR__ . '/initialize.php';



$arr = array(
	'key' => 'value',

	array(
		array(
			array(
				array(
					array('hello' => 'world'),
				),
			),
		),
	),

	(object) array(
		(object) array(
			(object) array(
				(object) array(
					(object) array('hello' => 'world'),
				),
			),
		),
	),
);


$obj = (object) $arr;

$obj->tmp = & $obj;

TestHelpers::dump( $obj );


$arr[] = & $arr;

TestHelpers::dump( $arr );



__halt_compiler() ?>
