<?php

/**
 * Test: ClassReflection tests.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\ClassReflection;



require __DIR__ . '/../initialize.php';



class Foo
{
	public function f() {}
}

class Bar extends Foo implements \Countable
{
	public $var;

	function count() {}
}


Assert::equal( new Nette\Reflection\ClassReflection('Bar'), ClassReflection::from('Bar') );
Assert::equal( new Nette\Reflection\ClassReflection('Bar'), ClassReflection::from(new Bar) );



$rc = ClassReflection::from('Bar');

Assert::null( $rc->getExtension() );


Assert::equal( array(
	'Countable' => new Nette\Reflection\ClassReflection('Countable'),
), $rc->getInterfaces() );


Assert::equal( new Nette\Reflection\ClassReflection('Foo'), $rc->getParentClass() );


Assert::null( $rc->getConstructor() );


Assert::equal( new Nette\Reflection\MethodReflection('Foo', 'f'), $rc->getMethod('f') );


try {
	$rc->getMethod('doesntExist');
} catch (Exception $e) {
	Assert::same( 'Method Bar::doesntExist() does not exist', $e->getMessage() );

}

Assert::equal( array(
	new Nette\Reflection\MethodReflection('Bar', 'count'),
	new Nette\Reflection\MethodReflection('Foo', 'f'),
), $rc->getMethods() );



Assert::equal( new Nette\Reflection\PropertyReflection('Bar', 'var'), $rc->getProperty('var') );


try {
	$rc->getProperty('doesntExist');
} catch (exception $e) {
	Assert::same( 'Property Bar::$doesntExist does not exist', $e->getMessage() );

}

Assert::equal( array(
	new Nette\Reflection\PropertyReflection('Bar', 'var'),
), $rc->getProperties() );
