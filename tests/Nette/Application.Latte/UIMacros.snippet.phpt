<?php

/**
 * Test: general snippets test.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Bridges\ApplicationLatte\UIMacros,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
UIMacros::install($latte->getCompiler());

Assert::matchFile(
	__DIR__ . '/expected/UIMacros.snippet.phtml',
	$latte->compile(__DIR__ . '/templates/snippet.latte')
);
