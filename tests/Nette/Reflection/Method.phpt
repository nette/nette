<?php

/**
 * Test: Nette\Reflection\Method tests.
 */

use Nette\Reflection,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class A {
	static function foo($a, $b) {
		return $a + $b;
	}
}

class B extends A {
	function bar($a, $b) {
		return $a - $b;
	}
}

$b = new B;
$method = new Reflection\Method($b, 'foo');
Assert::equal( new Reflection\ClassType('A'), $method->getDeclaringClass() );

Assert::null( $method->getExtension() );

Assert::same( 23, $method->getClosure(NULL)->__invoke(20, 3) );


$method = new Reflection\Method($b, 'bar');
Assert::same( 17, $method->getClosure($b)->__invoke(20, 3) );

$method = new Reflection\Method('B', 'foo');
Assert::same( 23, $method->toCallback()->invoke(20, 3) );
