<?php

/**
 * Test: Nette\ComponentModel\Container component named factory 4.
 *
 * @author     David Grudl
 * @package    Nette\ComponentModel
 */

use Nette\ComponentModel\Container;


require __DIR__ . '/../bootstrap.php';


class TestClass extends Container
{

	public function createComponentB($name)
	{
		return new self;
	}

}


$a = new TestClass;
$b = $a->getComponent('b');

Assert::same( 'b', $b->name );
Assert::same( 1, count($a->getComponents()) );


Assert::exception(function() use ($a) {
	$a->getComponent('B')->name;
}, 'InvalidArgumentException', "Component with name 'B' does not exist.");


$a->removeComponent($b);
Assert::same( 0, count($a->getComponents()) );
