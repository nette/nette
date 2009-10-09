<?php

/**
 * Test: Nette\Caching\Memcached expiration test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/
/*use Nette\Caching\MemcachedStorage;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



if (!MemcachedStorage::isAvailable()) {
	NetteTestHelpers::skip('Requires PHP extension Memcache.');
}



$key = 'nette';
$value = 'rulez';

$cache = new Cache(new MemcachedStorage('localhost'));


output('Writing cache...');
$cache->save($key, $value, array(
	Cache::EXPIRE => time() + 2,
));


for($i = 0; $i < 4; $i++) {
	output('Sleeping 1.2 second');
	usleep(1100000);
	dump( isset($cache[$key]), 'Is cached?' );
}



__halt_compiler();

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
