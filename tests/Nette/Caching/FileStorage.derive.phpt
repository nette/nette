<?php

/**
 * Test: Nette\Caching\Storages\FileStorage derive test.
 */

use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$key = 'nette';
$value = 'rulez';

$cache = new Cache(new FileStorage(TEMP_DIR), 'ns1');
$cache = $cache->derive('ns2');

$cache->save($key, $value);
Assert::same($cache->load($key), $value);
