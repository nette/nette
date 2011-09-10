<?php

/**
 * Test: Nette\ComponentModel\Container component named factory 4.
 *
 * @author     David Grudl
 * @package    Nette\ComponentModel
 * @subpackage UnitTests
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
Assert::same( 'b', $a->getComponent('b')->name );



Assert::throws(function() use ($a) {
	$a->getComponent('B')->name;
}, 'InvalidArgumentException', "Component with name 'B' does not exist.");
