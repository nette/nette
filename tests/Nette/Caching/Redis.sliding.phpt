<?php

/**
 * Test: Nette\Caching\Storages\RedisStorage sliding expiration test.
 *
 * @author     David Grudl, Ondřej Slámečka
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Storages\RedisStorage,
	Nette\Caching\Cache;



require __DIR__ . '/../bootstrap.php';



if (!RedisStorage::isAvailable()) {
	TestHelpers::skip('Requires PHP extension Redis.');
}



$key = 'nette-sliding-key';
$value = 'rulez';

$cache = new Cache(new RedisStorage('localhost'));


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
sleep(3);

Assert::false( isset($cache[$key]), 'Is cached?' );
