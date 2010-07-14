<?php

/**
 * Test: MethodReflection tests.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\MethodReflection;



require __DIR__ . '/../initialize.php';



class A {
	static function foo($a, $b) {
		return $a + $b;
	}
}

class B extends A {
	function bar() {}
}

$methodInfo = new MethodReflection('B', 'foo');
T::dump( $methodInfo->getDeclaringClass() );

T::dump( $methodInfo->getExtension() );

T::dump( $methodInfo->callback->invoke(20, 3) );



__halt_compiler() ?>

------EXPECT------
%ns%ClassReflection(
	"name" => "A"
)

NULL

23
