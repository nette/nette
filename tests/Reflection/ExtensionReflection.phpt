<?php

/**
 * Test: ExtensionReflection tests.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\ExtensionReflection;



require __DIR__ . '/../initialize.php';



$ext = new ExtensionReflection('standard');
$funcs = $ext->getFunctions();
Assert::equal( new Nette\Reflection\FunctionReflection('sleep'), $funcs['sleep'] );



$ext = new ExtensionReflection('reflection');
Assert::equal( array(
	'ReflectionException' => new Nette\Reflection\ClassReflection('ReflectionException'),
	'Reflection' => new Nette\Reflection\ClassReflection('Reflection'),
	'Reflector' => new Nette\Reflection\ClassReflection('Reflector'),
	'ReflectionFunctionAbstract' => new Nette\Reflection\ClassReflection('ReflectionFunctionAbstract'),
	'ReflectionFunction' => new Nette\Reflection\ClassReflection('ReflectionFunction'),
	'ReflectionParameter' => new Nette\Reflection\ClassReflection('ReflectionParameter'),
	'ReflectionMethod' => new Nette\Reflection\ClassReflection('ReflectionMethod'),
	'ReflectionClass' => new Nette\Reflection\ClassReflection('ReflectionClass'),
	'ReflectionObject' => new Nette\Reflection\ClassReflection('ReflectionObject'),
	'ReflectionProperty' => new Nette\Reflection\ClassReflection('ReflectionProperty'),
	'ReflectionExtension' => new Nette\Reflection\ClassReflection('ReflectionExtension'),
), $ext->getClasses() );
