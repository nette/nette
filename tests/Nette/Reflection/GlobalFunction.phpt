<?php

/**
 * Test: FunctionReflection tests.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection;



require __DIR__ . '/../bootstrap.php';



function foo($a, $b) {
	return $a + $b;
}

$function = new Reflection\GlobalFunction('sort');
Assert::equal( new Reflection\Extension('standard'), $function->getExtension() );


$function = new Reflection\GlobalFunction('foo');
Assert::null( $function->getExtension() );


Assert::same( 20, $function->invokeNamedArgs(array('b' => 20)) );


Assert::same( 23, $function->toCallback()->invoke(20, 3) );
