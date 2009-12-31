<?php

/**
 * Test: PropertyReflection tests.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

/*use Nette\Reflection\PropertyReflection;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



class A
{
    public $prop;
}

class B extends A
{
}

$propInfo = new PropertyReflection('B', 'prop');
dump( $propInfo->getDeclaringClass() );



__halt_compiler();

------EXPECT------
object(%ns%ClassReflection) (1) {
	"name" => string(1) "A"
}
