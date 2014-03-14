<?php

/**
 * Test: Nette\Reflection\Property tests.
 *
 * @author     David Grudl
 */

use Nette\Reflection,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class A
{
	public $prop;
}

class B extends A
{
}

$propInfo = new Reflection\Property('B', 'prop');
Assert::equal( new Reflection\ClassType('A'), $propInfo->getDeclaringClass() );
