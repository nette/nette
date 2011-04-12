<?php

/**
 * Test: Nette\ComponentContainer and '0' name.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\ComponentContainer;



require __DIR__ . '/../bootstrap.php';



$container = new ComponentContainer;
$container->addComponent(new ComponentContainer, 0);
Assert::same( '0', $container->getComponent(0)->getName() );
