<?php

/**
 * Test: Nette\ComponentModel\Container component factory & remove inside.
 *
 * @author     David Grudl
 */

use Nette\ComponentModel\Container,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestClass extends Container
{

	public function createComponentB($name)
	{
		$this->addComponent($b = new self, $name);
		$this->removeComponent($b);
		return new self;
	}

}


$a = new TestClass;
Assert::same( 'b', $a->getComponent('b')->name );
