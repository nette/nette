<?php

/**
 * Test: Nette\Reflection\Parameter tests.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection;



require __DIR__ . '/../bootstrap.php';



/**
 * @param string $test
 * @param string $test2
 */
function myFunction($test, $test2 = null) {
	echo $test;
}

$reflect = new Reflection\GlobalFunction('myFunction');
$params = $reflect->getParameters();
Assert::same( 2, count($params) );
Assert::same( 'myFunction()', (string) $params[0]->declaringFunction );
Assert::null( $params[0]->class );
Assert::null( $params[0]->declaringClass );
Assert::true( $params[0]->annotation instanceof Reflection\ParamAnnotation );
Assert::same( 'string $test', $params[0]->annotation->value );
Assert::same( 'myFunction()', (string) $params[1]->declaringFunction );
Assert::null( $params[1]->class );
Assert::null( $params[1]->declaringClass );
Assert::true( $params[1]->annotation instanceof Reflection\ParamAnnotation );
Assert::same( 'string $test2', $params[1]->annotation->value );



class Foo
{

	/**
	 * @param string $test
	 * @param string $test2
	 */
	function myMethod($test, $test2 = null)
	{
		echo $test;
	}



	/**
	 * @param Foo<Bar> $foo
	 * @param Foo $foo <Bar>
	 */
	function otherMethod(Foo $foo, Foo $foo)
	{

	}

}

$reflect = new Reflection\ClassType('Foo');
$params = $reflect->getMethod('myMethod')->getParameters();
Assert::same( 2, count($params) );
Assert::same( 'Foo::myMethod()', (string) $params[0]->declaringFunction );
Assert::null( $params[0]->class );
Assert::true( $params[0]->annotation instanceof Reflection\ParamAnnotation );
Assert::same( 'string $test', $params[0]->annotation->value );
Assert::same( 'Foo', (string) $params[0]->declaringClass );
Assert::same( 'Foo::myMethod()', (string) $params[1]->declaringFunction );
Assert::null( $params[1]->class );
Assert::same( 'Foo', (string) $params[1]->declaringClass );
Assert::true( $params[1]->annotation instanceof Reflection\ParamAnnotation );
Assert::same( 'string $test2', $params[1]->annotation->value );

$params = $reflect->getMethod('otherMethod')->getParameters();
Assert::same( 2, count($params) );
Assert::same( 'Foo<Bar>', $params[0]->getClassName(TRUE) );
Assert::same( 'Foo<Bar>', $params[1]->getClassName(TRUE) );
