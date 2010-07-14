<?php

/**
 * Test: ExtensionReflection tests.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\ExtensionReflection;



require __DIR__ . '/../initialize.php';



$ext = new ExtensionReflection("standard");
$funcs = $ext->getFunctions();
T::dump( $funcs['sleep'] );


$ext = new ExtensionReflection('reflection');
T::dump( $ext->getClasses() );



__halt_compiler() ?>

------EXPECT------
%ns%FunctionReflection(
	"name" => "sleep"
)

array(
	"ReflectionException" => %ns%ClassReflection(
		"name" => "ReflectionException"
	)
	"Reflection" => %ns%ClassReflection(
		"name" => "Reflection"
	)
	"Reflector" => %ns%ClassReflection(
		"name" => "Reflector"
	)
	"ReflectionFunctionAbstract" => %ns%ClassReflection(
		"name" => "ReflectionFunctionAbstract"
	)
	"ReflectionFunction" => %ns%ClassReflection(
		"name" => "ReflectionFunction"
	)
	"ReflectionParameter" => %ns%ClassReflection(
		"name" => "ReflectionParameter"
	)
	"ReflectionMethod" => %ns%ClassReflection(
		"name" => "ReflectionMethod"
	)
	"ReflectionClass" => %ns%ClassReflection(
		"name" => "ReflectionClass"
	)
	"ReflectionObject" => %ns%ClassReflection(
		"name" => "ReflectionObject"
	)
	"ReflectionProperty" => %ns%ClassReflection(
		"name" => "ReflectionProperty"
	)
	"ReflectionExtension" => %ns%ClassReflection(
		"name" => "ReflectionExtension"
	)
)
