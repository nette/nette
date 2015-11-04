<?php

/**
 * Test: Nette\Caching\Cache dead lock & exception test.
 */

use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$storage = new FileStorage(TEMP_DIR);
$cache = new Cache($storage);

try {
	$cache->load('key', function () {
		throw new Exception;
	});
} catch (Exception $e) {
}

Assert::noError(function () use ($cache) {
	$cache->load('key', function () {});
});
