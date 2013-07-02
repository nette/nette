<?php

/**
 * Test: Nette\Reflection\ClassType tests.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 */

use Nette\Reflection;


require __DIR__ . '/../bootstrap.php';


class Foo
{
	public function f() {}
}

class Bar extends Foo implements Countable
{
	public $var;

	function count() {}
}


Assert::equal( new Reflection\ClassType('Bar'), Reflection\ClassType::from('Bar') );
Assert::equal( new Reflection\ClassType('Bar'), Reflection\ClassType::from(new Bar) );


$rc = Reflection\ClassType::from('Bar');

Assert::null( $rc->getExtension() );


Assert::equal( array(
	'Countable' => new Reflection\ClassType('Countable'),
), $rc->getInterfaces() );


Assert::equal( new Reflection\ClassType('Foo'), $rc->getParentClass() );


Assert::null( $rc->getConstructor() );


Assert::equal( new Reflection\Method('Foo', 'f'), $rc->getMethod('f') );


Assert::exception(function() use ($rc) {
	$rc->getMethod('doesntExist');
}, 'ReflectionException', 'Method Bar::doesntExist() does not exist');

Assert::equal( array(
	new Reflection\Method('Bar', 'count'),
	new Reflection\Method('Foo', 'f'),
), $rc->getMethods() );


Assert::equal( new Reflection\Property('Bar', 'var'), $rc->getProperty('var') );


Assert::exception(function() use ($rc) {
	$rc->getProperty('doesntExist');
}, 'ReflectionException', 'Property Bar::$doesntExist does not exist');

Assert::equal( array(
	new Reflection\Property('Bar', 'var'),
), $rc->getProperties() );
