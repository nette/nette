<?php

/**
 * Test: Nette\Caching\Storages\MemcachedStorage connection status test.
 *
 * @author     Patrik Šíma
 * @package    Nette\Caching
 */

use Nette\Caching\Storages\MemcachedStorage,
	Nette\Caching\Storages\FileJournal,
	Nette\Caching\Cache;



require __DIR__ . '/../bootstrap.php';


if (!MemcachedStorage::isAvailable()) {
	Tester\Helpers::skip('Requires PHP extension Memcache.');
}


try {
	$cache = new Cache(new MemcachedStorage('localhost', 11666)); // assume that on this port by default is not running
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::true($e instanceof Nette\InvalidStateException);
	Assert::contains("Memcache::addServer()", $e->getMessage());
}
