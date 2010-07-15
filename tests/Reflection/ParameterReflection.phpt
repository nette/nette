<?php

/**
 * Test: ParameterReflection tests.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\ClassReflection,
	Nette\Reflection\FunctionReflection;



require __DIR__ . '/../initialize.php';



function myFunction($test, $test2 = null) {
	echo $test;
}

$reflect = new FunctionReflection('myFunction');
$params = $reflect->getParameters();

foreach($params as $key => $value) {
	echo $value->declaringFunction . ", ", $value->class, ", ", $value->declaringClass . "\n";
}



class Foo
{
	function myMethod($test, $test2 = null)
	{
		echo $test;
	}
}

$reflect = new ClassReflection('Foo');
$params = $reflect->getMethod('myMethod')->getParameters();

foreach($params as $key => $value) {
	echo $value->declaringFunction . ", ", $value->class, ", ", $value->declaringClass . "\n";
}



$reflect = new FunctionReflection(function ($x, $y) {});
$params = $reflect->getParameters();

foreach($params as $key => $value) {
	echo $value->declaringFunction . ", ", $value->class, ", ", $value->declaringClass . "\n";
}



__halt_compiler() ?>

------EXPECT------
Function myFunction(), ,
Function myFunction(), ,
Method Foo::myMethod(), , Class Foo
Method Foo::myMethod(), , Class Foo
Function {closure}(), ,
Function {closure}(), ,