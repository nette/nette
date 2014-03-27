<?php

/**
 * Test: Nette\Latte\Engine: {extends ...} test V.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';



$latte = new Latte\Engine;

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	$latte->compile(__DIR__ . '/templates/inheritance.child5.latte')
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/inheritance.child5.latte',
		array('ext' => 'inheritance.parent.latte')
	)
);
