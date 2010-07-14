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
%ns%ClassReflection(
	"name" => "Bar"
)

%ns%ClassReflection(
	"name" => "Bar"
)

%ns%ClassReflection(
	"name" => "Bar"
)

NULL

array(
	"Countable" => %ns%ClassReflection(
		"name" => "Countable"
	)
)

%ns%ClassReflection(
	"name" => "Foo"
)

NULL

%ns%MethodReflection(
	"name" => "f"
	"class" => "Foo"
)

Exception %ns%ReflectionException: Method doesntExist does not exist

array(
	%ns%MethodReflection(
		"name" => "count"
		"class" => "Bar"
	)
	%ns%MethodReflection(
		"name" => "f"
		"class" => "Foo"
	)
)

%ns%PropertyReflection(
	"name" => "var"
	"class" => "Bar"
)

Exception %ns%ReflectionException: Property doesntExist does not exist

array(
	%ns%PropertyReflection(
		"name" => "var"
		"class" => "Bar"
	)
)
