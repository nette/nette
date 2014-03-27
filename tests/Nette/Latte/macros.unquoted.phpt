<?php

/**
 * Test: Nette\Latte\Engine: unquoted attributes.
 *
 * @author     Jakub Vrana
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	$latte->compile(__DIR__ . '/templates/unquoted.latte')
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/unquoted.latte',
		array('x' => '\' & "')
	)
);
