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



require __DIR__ . '/../NetteTest/initialize.php';



class A {
	static function foo($a, $b) {
		return $a + $b;
	}
}

class B extends A {
	function bar() {}
}

$methodInfo = new MethodReflection('B', 'foo');
dump( $methodInfo->getDeclaringClass() );

dump( $methodInfo->getExtension() );

dump( $methodInfo->callback->invoke(20, 3) );



__halt_compiler() ?>

------EXPECT------
object(%ns%ClassReflection) (1) {
	"name" => string(1) "A"
}

NULL

int(23)
