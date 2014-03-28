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

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	$latte->compile(__DIR__ . '/templates/snippet.latte')
);
