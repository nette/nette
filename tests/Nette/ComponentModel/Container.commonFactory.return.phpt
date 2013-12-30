<?php

/**
 * Test: Nette\ComponentModel\Container component factory 1.
 *
 * @author     David Grudl
 */

use Nette\ComponentModel\Container,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestClass extends Container
{

	public function createComponent($name)
	{
		return new self;
	}

}


$a = new TestClass;
Assert::same( 'b', $a->getComponent('b')->name );
