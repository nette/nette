<?php

/**
 * Test: Nette\ComponentModel\Container and '0' name.
 */

use Nette\ComponentModel\Container,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$container = new Container;
$container->addComponent(new Container, 0);
Assert::same( '0', $container->getComponent(0)->getName() );
