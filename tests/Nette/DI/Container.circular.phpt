<?php

/**
 * Test: Nette\DI\Container circular reference detection.
 *
 * @author     David Grudl
 * @package    Nette
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

try {
	$container->getService('one');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', "Circular reference detected for services: one, two.", $e );
}
