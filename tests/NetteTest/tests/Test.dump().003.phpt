<?php

/**
 * Test: NetteTestHelpers::dump() nesting and recursion
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../initialize.php';


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

dump( $obj );


$arr[] = & $arr;

dump( $arr );


__halt_compiler();
