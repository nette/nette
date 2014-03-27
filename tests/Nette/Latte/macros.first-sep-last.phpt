<?php

/**
 * Test: Nette\Latte\Engine: {first}, {last}, {sep}.
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
	$latte->compile(__DIR__ . '/templates/first-sep-last.latte')
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/first-sep-last.latte',
		array('people' => array('John', 'Mary', 'Paul'))
	)
);
