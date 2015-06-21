<?php

/**
 * Test: Nette\Caching\Storages\FileStorage start().
 */

use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$cache = new Cache(new FileStorage(TEMP_DIR));


ob_start();
$block = $cache->start('key');
Assert::type('Nette\Caching\OutputHelper', $block);
echo 'Hello';
$block->end();
Assert::same('Hello', ob_get_clean());


Assert::same('Hello', $cache->load('key'));


ob_start();
Assert::null($cache->start('key'));
Assert::same('Hello', ob_get_clean());
