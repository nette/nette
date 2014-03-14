<?php

/**
 * Test: Nette\ComponentModel\Container component factory 3.
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
		$this->addComponent($component = new self, $name);
		return $component;
	}

}


$a = new TestClass;
Assert::same( 'b', $a->getComponent('b')->name );
