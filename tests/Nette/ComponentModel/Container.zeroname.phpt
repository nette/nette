<?php

/**
 * Test: Nette\ComponentModel\Container and '0' name.
 *
 * @author     David Grudl
 * @package    Nette\ComponentModel
 */

use Nette\ComponentModel\Container;


require __DIR__ . '/../bootstrap.php';


$container = new Container;
$container->addComponent(new Container, 0);
Assert::same( '0', $container->getComponent(0)->getName() );
