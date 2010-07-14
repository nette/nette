<?php

/**
 * Test: Annotations comment parser II.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\ClassReflection;



require __DIR__ . '/../initialize.php';



/**
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


$rc = new ClassReflection('TestClass1');
T::dump( $rc->getAnnotations() );

$rc = new ClassReflection('TestClass2');
T::dump( $rc->getAnnotations() );

$rc = new ClassReflection('TestClass3');
T::dump( $rc->getAnnotations() );



__halt_compiler() ?>

------EXPECT------
array(
	"one" => array(
		"value"
	)
	"two" => array(
		"value"
	)
	"three" => array(
		TRUE
	)
	"five" => array(
		TRUE
	)
	"brackets" => array(
		ArrayObject(
			"single" => "()@\'""
			"double" => "()@'\""
		)
	)
	"line1" => array(
		TRUE
	)
	"line2" => array(
		TRUE
	)
	"line3" => array(
		"value"
	)
	"line4" => array(
		TRUE
	)
)

array(
	"one" => array(
		"value"
	)
)

array(
	"one" => array(
		TRUE
	)
)
