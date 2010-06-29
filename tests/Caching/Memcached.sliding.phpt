<?php

/**
 * Test: Nette\Caching\Memcached sliding expiration test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache,
	Nette\Caching\MemcachedStorage;



require __DIR__ . '/../initialize.php';



if (!MemcachedStorage::isAvailable()) {
	T::skip('Requires PHP extension Memcache.');
}



$key = 'nette';
$value = 'rulez';

$cache = new Cache(new MemcachedStorage('localhost'));


T::note('Writing cache...');
$cache->save($key, $value, array(
	Cache::EXPIRE => time() + 2,
	Cache::SLIDING => TRUE,
));


for($i = 0; $i < 3; $i++) {
	T::note('Sleeping 1 second');
	sleep(1);
	$cache->release();
	T::dump( isset($cache[$key]), 'Is cached?' );
}

T::note('Sleeping few seconds...');
sleep(3);
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Sleeping 1 second

Is cached? bool(TRUE)

Sleeping 1 second

Is cached? bool(TRUE)

Sleeping 1 second

Is cached? bool(TRUE)

Sleeping few seconds...

Is cached? bool(FALSE)
