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



require __DIR__ . '/../NetteTest/initialize.php';



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
dump( $rc->getAnnotations() );

$rc = new ClassReflection('TestClass2');
dump( $rc->getAnnotations() );

$rc = new ClassReflection('TestClass3');
dump( $rc->getAnnotations() );



__halt_compiler() ?>

------EXPECT------
array(9) {
	"one" => array(1) {
		0 => string(5) "value"
	}
	"two" => array(1) {
		0 => string(5) "value"
	}
	"three" => array(1) {
		0 => bool(TRUE)
	}
	"five" => array(1) {
		0 => bool(TRUE)
	}
	"brackets" => array(1) {
		0 => object(ArrayObject) (2) {
			"single" => string(6) "()@\'""
			"double" => string(6) "()@'\""
		}
	}
	"line1" => array(1) {
		0 => bool(TRUE)
	}
	"line2" => array(1) {
		0 => bool(TRUE)
	}
	"line3" => array(1) {
		0 => string(5) "value"
	}
	"line4" => array(1) {
		0 => bool(TRUE)
	}
}

array(1) {
	"one" => array(1) {
		0 => string(5) "value"
	}
}

array(1) {
	"one" => array(1) {
		0 => bool(TRUE)
	}
}
