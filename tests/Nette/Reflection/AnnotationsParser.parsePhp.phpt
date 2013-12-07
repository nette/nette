<?php

/**
 * Test: Nette\Reflection\AnnotationsParser::parsePhp.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 */

use Nette\Reflection\AnnotationsParser;


require __DIR__ . '/../bootstrap.php';


Assert::same( array(
	'Test\AnnotatedClass1' => array(
		'class' => "/** @author john */",
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
	'Test\AnnotatedClass2' => array('class' => "/** @author jack */"),
), AnnotationsParser::parsePhp(__DIR__ . '/files/annotations.php') );


Assert::same( array(
	'Test\TestClass1' => array('use' => array('c' => 'A\B')),
	'Test\TestClass2' => array('use' => array('c' => 'A\B', 'd' => 'D', 'e' => 'E', 'h' => 'F\G')),
	'Test2\TestClass4' => array('use' => array('c' => 'A\B\C')),
), AnnotationsParser::parsePhp(__DIR__ . '/files/uses.php') );
