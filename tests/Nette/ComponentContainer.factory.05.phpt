<?php

/**
 * Test: Nette\ComponentContainer component named factory 5.
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
		new self($this, $name);
	}

}


$a = new TestClass;
dump( $a->getComponent('b')->name );


try {
	dump( $a->getComponent('B')->name );
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler() ?>

------EXPECT------
string(1) "b"

Exception InvalidArgumentException: Component with name 'B' does not exist.