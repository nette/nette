<?php

/**
 * Test: ClassReflection tests.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

/*use Nette\Reflection\ClassReflection;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



class Foo
{
	public function f() {}
}

class Bar extends Foo implements /*\*/Countable
{
	public $var;

	function count() {}
}


dump( new ClassReflection("Bar") );
dump( ClassReflection::from("Bar") );
dump( ClassReflection::from(new Bar) );


$rc = ClassReflection::from("Bar");

dump( $rc->getExtension() );

dump( $rc->getInterfaces() );

dump( $rc->getParentClass() );

dump( $rc->getConstructor() );

dump($rc->getMethod("f"));

try {
	dump($rc->getMethod("doesntExist"));
} catch (Exception $e) {
	dump($e);
}

dump( $rc->getMethods() );


dump($rc->getProperty("var"));

try {
	dump($rc->getProperty("doesntExist"));
} catch (exception $e) {
	dump($e);
}

dump( $rc->getProperties() );



__halt_compiler() ?>

------EXPECT------
object(%ns%ClassReflection) (1) {
	"name" => string(3) "Bar"
}

object(%ns%ClassReflection) (1) {
	"name" => string(3) "Bar"
}

object(%ns%ClassReflection) (1) {
	"name" => string(3) "Bar"
}

NULL

array(1) {
	"Countable" => object(%ns%ClassReflection) (1) {
		"name" => string(9) "Countable"
	}
}

object(%ns%ClassReflection) (1) {
	"name" => string(3) "Foo"
}

NULL

object(%ns%MethodReflection) (2) {
	"name" => string(1) "f"
	"class" => string(3) "Foo"
}

Exception %ns%ReflectionException: Method doesntExist does not exist

array(2) {
	0 => object(%ns%MethodReflection) (2) {
		"name" => string(5) "count"
		"class" => string(3) "Bar"
	}
	1 => object(%ns%MethodReflection) (2) {
		"name" => string(1) "f"
		"class" => string(3) "Foo"
	}
}

object(%ns%PropertyReflection) (2) {
	"name" => string(3) "var"
	"class" => string(3) "Bar"
}

Exception %ns%ReflectionException: Property doesntExist does not exist

array(1) {
	0 => object(%ns%PropertyReflection) (2) {
		"name" => string(3) "var"
		"class" => string(3) "Bar"
	}
}
