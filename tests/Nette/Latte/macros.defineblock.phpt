<?php

/**
 * Test: Nette\Latte\Engine: {define ...}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;

Assert::matchFile(
	__DIR__ . '/expected/macros.defineblock.phtml',
	$latte->compile(__DIR__ . '/templates/defineblock.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/macros.defineblock.html',
	$latte->renderToString(__DIR__ . '/templates/defineblock.latte')
);
