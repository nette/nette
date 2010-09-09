<?php

/**
 * Test: PropertyReflection tests.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\PropertyReflection;



require __DIR__ . '/../initialize.php';



class A
{
	public $prop;
}

class B extends A
{
}

$propInfo = new PropertyReflection('B', 'prop');
Assert::equal( new Nette\Reflection\ClassReflection('A'), $propInfo->getDeclaringClass() );
