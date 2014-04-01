<?php

/**
 * Test: Latte\Engine: general HTML test.
 *
 * @author     David Grudl
 */

use Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;

Assert::matchFile(
	__DIR__ . '/expected/macros.recursive.phtml',
	$latte->compile(__DIR__ . '/templates/recursive.latte')
);
