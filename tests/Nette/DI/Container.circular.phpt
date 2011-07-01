<?php

/**
 * Test: Nette\DI\Container circular reference detection.
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



class MyContainer extends Container
{

	protected function createServiceOne()
	{
		return $this->two;
	}

	protected function createServiceTwo()
	{
		return $this->one;
	}

}



$container = new MyContainer;

Assert::throws(function() use ($container) {
	$container->getService('one');
}, 'Nette\InvalidStateException', "Circular reference detected for services: one, two.");
