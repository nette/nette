<?php

/**
 * Test: Nette\ComponentContainer component factory & remove inside.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\ComponentContainer;



require __DIR__ . '/../bootstrap.php';



class TestClass extends ComponentContainer
{

	public function createComponentB($name)
	{
		$b = new self($this, $name);
		$this->removeComponent($b);
		return new self;
	}

}


$a = new TestClass;
Assert::same( 'b', $a->getComponent('b')->name );
