<?php

/**
 * Test: Nette\ComponentModel\Container component named factory 5.
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
		new self($this, $name);
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
