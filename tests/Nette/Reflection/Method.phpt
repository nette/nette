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

$methodInfo = new Reflection\Method('B', 'foo');
Assert::equal( new Reflection\ClassType('A'), $methodInfo->getDeclaringClass() );


Assert::null( $methodInfo->getExtension() );


Assert::same( 23, $methodInfo->callback->invoke(20, 3) );
