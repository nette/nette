<?php

/**
 * Test: Nette\Caching\Memcached expiration test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache,
	Nette\Caching\MemcachedStorage;



require __DIR__ . '/../initialize.php';



if (!MemcachedStorage::isAvailable()) {
	TestHelpers::skip('Requires PHP extension Memcache.');
}



$key = 'nette';
$value = 'rulez';

$cache = new Cache(new MemcachedStorage('localhost'));


// Writing cache...
$cache->save($key, $value, array(
	Cache::EXPIRE => time() + 3,
));


// Sleeping 1 second
sleep(1);
$cache->release();
Assert::true( isset($cache[$key]), 'Is cached?' );



// Sleeping 3 seconds
sleep(3);
$cache->release();
Assert::false( isset($cache[$key]), 'Is cached?' );
