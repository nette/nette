<?php

/**
 * Test: {cache ...}
 *
 * @author     David Grudl
 */

use Nette\Bridges\CacheLatte\CacheMacro,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setTempDirectory(TEMP_DIR);
$latte->addMacro('cache', new CacheMacro($latte->getCompiler()));

$params['netteCacheStorage'] = new Nette\Caching\Storages\DevNullStorage;
$params['title'] = 'Hello';
$params['id'] = 456;

Assert::matchFile(
	__DIR__ . '/expected/CacheMacro.cache.phtml',
	$latte->compile(__DIR__ . '/templates/cache.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/CacheMacro.cache.html',
	$latte->renderToString(
		__DIR__ . '/templates/cache.latte',
		$params
	)
);
Assert::matchFile(
	__DIR__ . '/expected/CacheMacro.cache.inc.phtml',
	file_get_contents($latte->getCacheFile(__DIR__ . '/templates/include.cache.latte'))
);
