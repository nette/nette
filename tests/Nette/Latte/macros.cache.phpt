<?php

/**
 * Test: Nette\Latte\Engine: {cache ...}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$latte = new Latte\Engine;
$latte->cacheStorage = new MockCacheStorage;
$latte->addFilter(NULL, 'Nette\Latte\Runtime\Filters::loader');

$params['netteCacheStorage'] = new Nette\Caching\Storages\DevNullStorage;
$params['title'] = 'Hello';
$params['id'] = 456;

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	$latte->compile(__DIR__ . '/templates/cache.latte')
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/cache.latte',
		$params
	)
);
Assert::matchFile("$path.inc.phtml", $latte->cacheStorage->phtml['include.cache.latte']);
