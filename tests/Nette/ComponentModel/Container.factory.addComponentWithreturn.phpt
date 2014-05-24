<?php

/**
 * Test: Nette\ComponentModel\Container component named factory 6.
 */

use Nette\ComponentModel\Container,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestClass extends Container
{

	public function createComponentB($name)
	{
		$this->addComponent($component = new self, $name);
		return $component;
	}

}


$a = new TestClass;
Assert::same( 'b', $a->getComponent('b')->name );


Assert::exception(function() use ($a) {
	$a->getComponent('B')->name;
}, 'InvalidArgumentException', "Component with name 'B' does not exist.");
