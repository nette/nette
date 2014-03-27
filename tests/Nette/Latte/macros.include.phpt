<?php

/**
 * Test: Nette\Latte\Engine: {include file}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setTempDirectory(TEMP_DIR);
$latte->addFilter(NULL, 'Nette\Latte\Runtime\Filters::loader');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	$latte->compile(__DIR__ . '/templates/include.latte')
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/include.latte',
		array('hello' => '<i>Hello</i>')
	)
);
Assert::matchFile("$path.inc1.phtml", file_get_contents($latte->getCacheFile(__DIR__ . '/templates/subdir/include1.latte')));
Assert::matchFile("$path.inc2.phtml", file_get_contents($latte->getCacheFile(__DIR__ . '/templates/subdir/include2.latte')));
Assert::matchFile("$path.inc3.phtml", file_get_contents($latte->getCacheFile(__DIR__ . '/templates/subdir/../include3.latte')));
