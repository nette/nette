<?php

/**
 * Test: Nette\Reflection\Extension tests.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection;



require __DIR__ . '/../bootstrap.php';



$ext = new Reflection\Extension('standard');
$funcs = $ext->getFunctions();
Assert::equal( new Reflection\GlobalFunction('sleep'), $funcs['sleep'] );



$ext = new Reflection\Extension('reflection');
Assert::equal( array(
	'ReflectionException' => new Reflection\ClassType('ReflectionException'),
	'Reflection' => new Reflection\ClassType('Reflection'),
	'Reflector' => new Reflection\ClassType('Reflector'),
	'ReflectionFunctionAbstract' => new Reflection\ClassType('ReflectionFunctionAbstract'),
	'ReflectionFunction' => new Reflection\ClassType('ReflectionFunction'),
	'ReflectionParameter' => new Reflection\ClassType('ReflectionParameter'),
	'ReflectionMethod' => new Reflection\ClassType('ReflectionMethod'),
	'ReflectionClass' => new Reflection\ClassType('ReflectionClass'),
	'ReflectionObject' => new Reflection\ClassType('ReflectionObject'),
	'ReflectionProperty' => new Reflection\ClassType('ReflectionProperty'),
	'ReflectionExtension' => new Reflection\ClassType('ReflectionExtension'),
), $ext->getClasses() );
