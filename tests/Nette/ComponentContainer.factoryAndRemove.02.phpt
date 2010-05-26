<?php

/**
 * Test: Nette\ComponentContainer component factory & remove inside.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\ComponentContainer;



require __DIR__ . '/../NetteTest/initialize.php';



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
dump( $a->getComponent('b')->name );



__halt_compiler() ?>

------EXPECT------
string(1) "b"
