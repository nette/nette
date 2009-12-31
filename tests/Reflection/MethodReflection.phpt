<?php

/**
 * Test: MethodReflection tests.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

/*use Nette\Reflection\MethodReflection;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



class A {
    function foo() {}
}

class B extends A {
    function bar() {}
}

$methodInfo = new MethodReflection('B', 'foo');
dump( $methodInfo->getDeclaringClass() );

dump( $methodInfo->getExtension() );



__halt_compiler();

------EXPECT------
object(%ns%ClassReflection) (1) {
	"name" => string(1) "A"
}

NULL
