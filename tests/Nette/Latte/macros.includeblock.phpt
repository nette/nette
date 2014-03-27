<?php

/**
 * Test: Nette\Latte\Engine: {includeblock ...}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$latte = new Latte\Engine;
$latte->cacheStorage = new MockCacheStorage;

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	$latte->compile(__DIR__ . '/templates/includeblock.latte')
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(__DIR__ . '/templates/includeblock.latte')
);
Assert::matchFile("$path.inc.phtml", $latte->cacheStorage->phtml['includeblock.inc.latte']);
