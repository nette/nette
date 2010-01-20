<?php

/**
 * Test: Nette\ComponentContainer component named factory.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\ComponentContainer;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



class TestClass extends ComponentContainer
{

	public function createComponentB()
	{
		return new self();
	}

}


$a = new TestClass;
$b = $a->getComponent('b');

dump( count($a->getComponents()) );

$a->removeComponent($b);

dump( count($a->getComponents()) );



__halt_compiler();

------EXPECT------
int(1)

int(0)
