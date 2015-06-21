<?php

/**
 * Test: Nette\DI\Container static & dynamic usage.
 */

use Nette\DI\Container;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MyContainer extends Container
{

	protected function createServiceOne()
	{
		return NULL;
	}

}


$container = new MyContainer;

Assert::true($container->hasService('one'));

$container->addService('one', new stdClass);

Assert::true($container->hasService('one'));

Assert::type('stdClass', $container->getService('one'));
Assert::same($container->getService('one'), $container->getService('one')); // shared
