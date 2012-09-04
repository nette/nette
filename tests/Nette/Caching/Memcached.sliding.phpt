<?php

/**
 * Test: Nette\Caching\Storages\MemcachedStorage sliding expiration test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Storages\MemcachedStorage,
	Nette\Caching\Cache;



require __DIR__ . '/../bootstrap.php';



if (!MemcachedStorage::isAvailable()) {
	TestHelpers::skip('Requires PHP extension Memcache.');
}



$key = 'nette-sliding-key';
$value = 'rulez';

$cache = new Cache(new MemcachedStorage('localhost'));


// Writing cache...
$cache->save($key, $value, array(
	Cache::EXPIRATION => time() + 2,
	Cache::SLIDING => TRUE,
));


for($i = 0; $i < 3; $i++) {
	// Sleeping 1 second
	sleep(1);
	Assert::true( isset($cache[$key]), 'Is cached?' );

}

// Sleeping few seconds...
sleep(4);

Assert::false( isset($cache[$key]), 'Is cached?' );
