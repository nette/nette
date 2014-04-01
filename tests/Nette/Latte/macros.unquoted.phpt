<?php

/**
 * Test: Latte\Engine: unquoted attributes.
 *
 * @author     Jakub Vrana
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;

Assert::matchFile(
	__DIR__ . '/expected/macros.unquoted.phtml',
	$latte->compile(__DIR__ . '/templates/unquoted.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/macros.unquoted.html',
	$latte->renderToString(
		__DIR__ . '/templates/unquoted.latte',
		array('x' => '\' & "')
	)
);
