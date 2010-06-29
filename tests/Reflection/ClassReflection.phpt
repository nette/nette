<?php

/**
 * Test: ClassReflection tests.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\ClassReflection;



require __DIR__ . '/../initialize.php';



class Foo
{
	public function f() {}
}

class Bar extends Foo implements \Countable
{
	public $var;

	function count() {}
}


T::dump( new ClassReflection("Bar") );
T::dump( ClassReflection::from("Bar") );
T::dump( ClassReflection::from(new Bar) );


$rc = ClassReflection::from("Bar");

T::dump( $rc->getExtension() );

T::dump( $rc->getInterfaces() );

T::dump( $rc->getParentClass() );

T::dump( $rc->getConstructor() );

T::dump($rc->getMethod("f"));

try {
	T::dump($rc->getMethod("doesntExist"));
} catch (Exception $e) {
	T::dump($e);
}

T::dump( $rc->getMethods() );


T::dump($rc->getProperty("var"));

try {
	T::dump($rc->getProperty("doesntExist"));
} catch (exception $e) {
	T::dump($e);
}

T::dump( $rc->getProperties() );



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
