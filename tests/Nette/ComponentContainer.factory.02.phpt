<?php

/**
 * Test: Nette\ComponentContainer component factory 2.
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
		new self($this, $name);
	}

}


$a = new TestClass;
T::dump( $a->getComponent('b')->name );



__halt_compiler() ?>

------EXPECT------
string(1) "b"