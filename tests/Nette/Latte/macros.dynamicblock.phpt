<?php

/**
 * Test: Nette\Latte\Engine: {block $name} dynamic blocks.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;

Assert::matchFile(
	__DIR__ . '/expected/macros.dynamicblock.phtml',
	$latte->compile(__DIR__ . '/templates/dynamicblocks.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/macros.dynamicblock.html',
	$latte->renderToString(__DIR__ . '/templates/dynamicblocks.latte')
);
