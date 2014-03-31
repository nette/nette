<?php

/**
 * Test: Nette\Latte\Engine: {syntax ...}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;

Assert::matchFile(
	__DIR__ . '/expected/macros.syntax.phtml',
	$latte->compile(__DIR__ . '/templates/syntax.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/macros.syntax.html',
	$latte->renderToString(
		__DIR__ . '/templates/syntax.latte',
		array('people' => array('John', 'Mary', 'Paul'))
	)
);
