<?php

/**
 * Test: dynamic snippets test.
 *
 * @author     David Grudl
 */

use Nette\Bridges\ApplicationLatte\UIMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
UIMacros::install($latte->getCompiler());

Assert::matchFile(
	__DIR__ . '/expected/UIMacros.dynamicsnippets.phtml',
	$latte->compile(__DIR__ . '/templates/dynamicsnippets.latte')
);
