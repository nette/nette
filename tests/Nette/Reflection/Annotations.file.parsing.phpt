<?php

/**
 * Test: Nette\Reflection\AnnotationsParser file parser.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 */

use Nette\Reflection\AnnotationsParser;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/files/annotations.php';


AnnotationsParser::$useReflection = FALSE;


test(function() { // AnnotatedClass1
	$rc = new ReflectionClass('Test\AnnotatedClass1');
	Assert::same( array(
		'author' => array('john'),
	), AnnotationsParser::getAll($rc) );

	Assert::same( array(
		'var' => array('a'),
	), AnnotationsParser::getAll($rc->getProperty('a')), '$a' );

	Assert::same( array(
		'var' => array('b'),
	), AnnotationsParser::getAll($rc->getProperty('b')), '$b' );

	Assert::same( array(
		'var' => array('c'),
	), AnnotationsParser::getAll($rc->getProperty('c')), '$c' );

	Assert::same( array(
		'var' => array('d'),
	), AnnotationsParser::getAll($rc->getProperty('d')), '$d' );

	Assert::same( array(
		'var' => array('e'),
	), AnnotationsParser::getAll($rc->getProperty('e')), '$e' );

	Assert::same( array(), AnnotationsParser::getAll($rc->getProperty('f')) );

	// Nette\Reflection\AnnotationsParser::getAll($rc->getProperty('g')), '$g' ); // ignore due PHP bug #50174
	Assert::same( array(
		'return' => array('a'),
	), AnnotationsParser::getAll($rc->getMethod('a')), 'a()' );

	Assert::same( array(
		'return' => array('b'),
	), AnnotationsParser::getAll($rc->getMethod('b')), 'b()' );

	Assert::same( array(
		'return' => array('c'),
	), AnnotationsParser::getAll($rc->getMethod('c')), 'c()' );

	Assert::same( array(
		'return' => array('d'),
	), AnnotationsParser::getAll($rc->getMethod('d')), 'd()' );

	Assert::same( array(
		'return' => array('e'),
	), AnnotationsParser::getAll($rc->getMethod('e')), 'e()' );

	Assert::same( array(), AnnotationsParser::getAll($rc->getMethod('f')) );

	Assert::same( array(
		'return' => array('g'),
	), AnnotationsParser::getAll($rc->getMethod('g')), 'g()' );
});


test(function() { // AnnotatedClass2
	$rc = new ReflectionClass('Test\AnnotatedClass2');
	Assert::same( array(
		'author' => array('jack'),
	), AnnotationsParser::getAll($rc) );
});


test(function() { // AnnotatedClass3
	$rc = new ReflectionClass('Test\AnnotatedClass3');
	Assert::same( array(), AnnotationsParser::getAll($rc) );
});
