<?php

/**
 * Test: ExtensionReflection tests.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

/*use Nette\Reflection\ExtensionReflection;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$ext = new ExtensionReflection("standard");
$funcs = $ext->getFunctions();
dump( $funcs['sleep'] );


$ext = new ExtensionReflection('reflection');
dump( $ext->getClasses() );



__halt_compiler() ?>

------EXPECT------
object(%ns%FunctionReflection) (1) {
	"name" => string(5) "sleep"
}

array(11) {
	"ReflectionException" => object(%ns%ClassReflection) (1) {
		"name" => string(19) "ReflectionException"
	}
	"Reflection" => object(%ns%ClassReflection) (1) {
		"name" => string(10) "Reflection"
	}
	"Reflector" => object(%ns%ClassReflection) (1) {
		"name" => string(9) "Reflector"
	}
	"ReflectionFunctionAbstract" => object(%ns%ClassReflection) (1) {
		"name" => string(26) "ReflectionFunctionAbstract"
	}
	"ReflectionFunction" => object(%ns%ClassReflection) (1) {
		"name" => string(18) "ReflectionFunction"
	}
	"ReflectionParameter" => object(%ns%ClassReflection) (1) {
		"name" => string(19) "ReflectionParameter"
	}
	"ReflectionMethod" => object(%ns%ClassReflection) (1) {
		"name" => string(16) "ReflectionMethod"
	}
	"ReflectionClass" => object(%ns%ClassReflection) (1) {
		"name" => string(15) "ReflectionClass"
	}
	"ReflectionObject" => object(%ns%ClassReflection) (1) {
		"name" => string(16) "ReflectionObject"
	}
	"ReflectionProperty" => object(%ns%ClassReflection) (1) {
		"name" => string(18) "ReflectionProperty"
	}
	"ReflectionExtension" => object(%ns%ClassReflection) (1) {
		"name" => string(19) "ReflectionExtension"
	}
}
