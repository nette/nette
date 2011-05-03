<?php

/**
 * Test: MethodReflection tests.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection;



require __DIR__ . '/../bootstrap.php';



class A {
	static function foo($a, $b) {
		return $a + $b;
	}
}

class B extends A {
	function bar() {}
}

$method = new Reflection\Method('B', 'foo');
Assert::equal( new Reflection\ClassType('A'), $method->getDeclaringClass() );


Assert::null( $method->getExtension() );


Assert::same( 20, $method->invokeNamedArgs(NULL, array('b' => 20)) );


Assert::same( 23, $method->toCallback()->invoke(20, 3) );
