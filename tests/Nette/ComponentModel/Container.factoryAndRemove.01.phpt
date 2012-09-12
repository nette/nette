<?php

/**
 * Test: Nette\ComponentModel\Container component named factory.
 *
 * @author     David Grudl
 * @package    Nette\ComponentModel
 */

use Nette\ComponentModel\Container;



require __DIR__ . '/../bootstrap.php';



class TestClass extends Container
{

	public function createComponentB()
	{
		return new self();
	}

}


$a = new TestClass;
$b = $a->getComponent('b');

Assert::same( 1, count($a->getComponents()) );


$a->removeComponent($b);

Assert::same( 0, count($a->getComponents()) );
