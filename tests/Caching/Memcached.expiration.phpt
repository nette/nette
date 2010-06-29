<?php

/**
 * Test: Nette\Caching\Memcached expiration test.
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
));


for($i = 0; $i < 4; $i++) {
	T::note('Sleeping 1.2 second');
	usleep(1200000);
	$cache->release();
	T::dump( isset($cache[$key]), 'Is cached?' );
}



__halt_compiler() ?>

------EXPECT------
Writing cache...

Sleeping 1.2 second

Is cached? bool(TRUE)

Sleeping 1.2 second

Is cached? bool(FALSE)

Sleeping 1.2 second

Is cached? bool(FALSE)

Sleeping 1.2 second

Is cached? bool(FALSE)
