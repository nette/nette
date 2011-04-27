<?php

/**
 * Test: Annotations comment parser II.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection;



require __DIR__ . '/../bootstrap.php';



/**
 * This is my favorite class.
 * @one( value), out
 * @two (value)
 * @three(
 * @4th
 * @five
 * @brackets( single = '()@\'"', double = "()@'\"")
 * @line1() @line2 @line3 value @line4
 */
class TestClass1 {
}

/** @one(value)*/
class TestClass2 {
}

/** @one*/
class TestClass3 {
}


$rc = new Reflection\ClassType('TestClass1');
Assert::equal( array(
	'description' => array('This is my favorite class.'),
	'one' => array('value'),
	'two' => array('value'),
	'three' => array(TRUE),
	'five' => array(TRUE),
	'brackets' => array(
		new ArrayObject(array(
			'single' => "()@'\"",
			'double' => "()@'\"",
		)),
	),
	'line1' => array(TRUE),
	'line2' => array(TRUE),
	'line3' => array('value'),
	'line4' => array(TRUE),
), $rc->getAnnotations() );


$rc = new Reflection\ClassType('TestClass2');
Assert::same( array(
	'one' => array('value'),
), $rc->getAnnotations() );


$rc = new Reflection\ClassType('TestClass3');
Assert::same( array(
	'one' => array(TRUE),
), $rc->getAnnotations() );
