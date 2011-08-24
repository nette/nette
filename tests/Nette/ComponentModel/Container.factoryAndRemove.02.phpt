<?php

/**
 * Test: Nette\ComponentModel\Container component factory & remove inside.
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
		$b = new self($this, $name);
		$this->removeComponent($b);
		return new self;
	}

}


$a = new TestClass;
$b = Assert::triggers(function () use ($a) {
	return $a->getComponent('b');
}, E_USER_WARNING, 'Attaching components to parent using Nette\ComponentModel\Component::__construct() is deprecated; use $parent->addComponent($component, $name) or component factory instead.');
Assert::same( 'b', $b->name );
