<?php

/**
 * Test: Nette\Caching\Storages\MemcachedStorage sliding expiration test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Storages\MemcachedStorage,
	Nette\Caching\Cache;



require __DIR__ . '/../bootstrap.php';



if (!MemcachedStorage::isAvailable()) {
	Tester\Helpers::skip('Requires PHP extension Memcache.');
}



$key = 'nette-sliding-key';
$value = 'rulez';

$cache = new Cache(new MemcachedStorage('localhost'));


// Writing cache...
$cache->save($key, $value, array(
	Cache::EXPIRATION => time() + 3,
	Cache::SLIDING => TRUE,
));


for ($i = 0; $i < 5; $i++) {
	// Sleeping 1 second
	sleep(1);

	Assert::true( isset($cache[$key]), 'Is cached?' );

}

// Sleeping few seconds...
sleep(5);

Assert::false( isset($cache[$key]), 'Is cached?' );
