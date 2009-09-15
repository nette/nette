<?php

/**
 * Test: Memcached expiration test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$key = 'nette';
$value = 'rulez';

$cache = new Cache(new /*Nette\Caching\*/MemcachedStorage('localhost'));


message('Writing cache...');
$cache->save($key, $value, array(
	Cache::EXPIRE => time() + 2,
));


for($i = 0; $i < 4; $i++) {
	message('Sleeping 1.2 second');
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
