<?php

/**
 * Test: Nette\Reflection\AnnotationsParser::parsePhp.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 */

use Nette\Reflection\AnnotationsParser,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( array(
	'Test\AnnotatedClass1' => array(
		'class' => '/** @author john */',
		'$a' => '/** @var a */',
		'$b' => '/** @var b */',
		'$c' => '/** @var c */',
		'$d' => '/** @var d */',
		'$e' => '/** @var e */',
		'a' => '/** @return a */',
		'b' => '/** @return b */',
		'c' => '/** @return c */',
		'd' => '/** @return d */',
		'e' => '/** @return e */',
		'g' => '/** @return g */',
	),
	'Test\AnnotatedClass2' => array('class' => '/** @author jack */'),
), AnnotationsParser::parsePhp(file_get_contents(__DIR__ . '/files/annotations.php')) );


Assert::same( array(
	'Test\TestClass1' => array('use' => array('C' => 'A\B')),
	'Test\TestClass2' => array('use' => array('C' => 'A\B', 'D' => 'D', 'E' => 'E', 'H' => 'F\G')),
	'Test2\TestClass4' => array('use' => array('C' => 'A\B\C')),
), AnnotationsParser::parsePhp(file_get_contents(__DIR__ . '/files/uses.php')) );
