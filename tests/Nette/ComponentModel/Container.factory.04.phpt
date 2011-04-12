<?php

/**
 * Test: Nette\ComponentContainer component named factory 4.
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
		return new self;
	}

}


$a = new TestClass;
Assert::same( 'b', $a->getComponent('b')->name );



try {
	$a->getComponent('B')->name;
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Component with name 'B' does not exist.", $e );
}
