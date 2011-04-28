<?php

/**
 * Test: Annotations inheritance.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection;



require __DIR__ . '/../bootstrap.php';



interface IA {

	/** This is IA */
	function __construct();

	/**
	 * This is IA
	 * @return mixed
	 * @author John
	 */
	function foo();
}

class A implements IA {

	/** @inheritdoc */
	function __construct() {}

	/** @inheritdoc */
	function foo() {}

	/** This is A */
	private function bar() {}

	/** @inheritdoc */
	function foobar() {}
}

class B extends A {

	function __construct() {}

	/** This is B */
	function foo() {}

	private function bar() {}
}



// constructors
$method = new Reflection\Method('B', '__construct');
Assert::null( $method->getAnnotation('description') );

$method = new Reflection\Method('A', '__construct');
Assert::same( 'This is IA', $method->getAnnotation('description') );


// public method
$method = new Reflection\Method('B', 'foo');
Assert::same( 'This is B', $method->getAnnotation('description') );
Assert::same( 'mixed', $method->getAnnotation('return') );
Assert::null( $method->getAnnotation('author') );

$method = new Reflection\Method('A', 'foo');
Assert::same( 'This is IA', $method->getAnnotation('description') );
Assert::same( 'mixed', $method->getAnnotation('return') );
Assert::null( $method->getAnnotation('author') );

$method = new Reflection\Method('IA', 'foo');
Assert::same( 'This is IA', $method->getAnnotation('description') );
Assert::same( 'mixed', $method->getAnnotation('return') );
Assert::same( 'John', $method->getAnnotation('author') );


// private method
$method = new Reflection\Method('B', 'bar');
Assert::null( $method->getAnnotation('description') );


// @inheritdoc
$method = new Reflection\Method('B', 'foobar');
Assert::null( $method->getAnnotation('description') );
