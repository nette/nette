<?php

/**
 * Test: Nette\ComponentContainer component factory 3.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\ComponentContainer;



require __DIR__ . '/../initialize.php';



class TestClass extends ComponentContainer
{

	public function createComponent($name)
	{
		return new self($this, $name);
	}

}


$a = new TestClass;
Assert::same( 'b', $a->getComponent('b')->name );
