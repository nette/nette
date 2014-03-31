<?php

/**
 * Test: Nette\Latte\Engine: {extends ...} test II.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;

Assert::matchFile(
	__DIR__ . '/expected/macros.inheritance.child2.phtml',
	$latte->compile(__DIR__ . '/templates/inheritance.child2.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/macros.inheritance.child2.html',
	$latte->renderToString(
		__DIR__ . '/templates/inheritance.child2.latte',
		array('people' => array('John', 'Mary', 'Paul'))
	)
);
