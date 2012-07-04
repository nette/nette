<?php

/**
 * Test: Nette\Reflection & annotations.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\ParamAnnotation;



require __DIR__ . '/../bootstrap.php';



// type
Assert::equal( array(
	'value' => 'string',
	'type' => 'string',
	'name' => '',
	'description' => '',
	'generic' => ''
), (array) new ParamAnnotation(array('value' => 'string')) );

// name
Assert::equal( array(
	'value' => 'string $abc',
	'type' => 'string',
	'name' => 'abc',
	'description' => '',
	'generic' => ''
), (array) new ParamAnnotation(array('value' => 'string $abc')) );

// description
Assert::equal( array(
	'value' => 'string $abc blabla',
	'type' => 'string',
	'name' => 'abc',
	'description' => 'blabla',
	'generic' => ''
), (array) new ParamAnnotation(array('value' => 'string $abc blabla')) );

// generics
Assert::equal( array(
	'value' => 'Foo<Bar> $abc blabla',
	'type' => 'Foo',
	'name' => 'abc',
	'description' => 'blabla',
	'generic' => 'Bar'
), (array) new ParamAnnotation(array('value' => 'Foo<Bar> $abc blabla')) );

// generics
Assert::equal( array(
	'value' => 'Foo $abc <Bar> blabla',
	'type' => 'Foo',
	'name' => 'abc',
	'description' => 'blabla',
	'generic' => 'Bar'
), (array) new ParamAnnotation(array('value' => 'Foo $abc <Bar> blabla')) );

