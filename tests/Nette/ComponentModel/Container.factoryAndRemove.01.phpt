<?php

/**
 * Test: Nette\ComponentContainer component named factory.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\ComponentContainer;



require __DIR__ . '/../bootstrap.php';



class TestClass extends ComponentContainer
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
