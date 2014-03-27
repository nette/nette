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

require __DIR__ . '/Template.inc';


$latte = new Latte\Engine;
$latte->cacheStorage = new MockCacheStorage;
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
Assert::matchFile("$path.inc1.phtml", $latte->cacheStorage->phtml['include1.latte']);
Assert::matchFile("$path.inc2.phtml", $latte->cacheStorage->phtml['include2.latte']);
Assert::matchFile("$path.inc3.phtml", $latte->cacheStorage->phtml['include3.latte']);


Assert::exception(function() {
	$latte = new Latte\Engine;
	$latte->setLoader(new Latte\Loaders\StringLoader);
	$latte->renderToString('{include somefile.latte}');
}, 'Nette\NotSupportedException', 'Macro {include "filename"} is supported only with Nette\Templating\IFileTemplate.');
