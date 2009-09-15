<?php

/**
 * Test: Memcached sliding expiration test.
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
	Cache::SLIDING => TRUE,
));


for($i = 0; $i < 3; $i++) {
	message('Sleeping 1 second');
	sleep(1);
	dump( isset($cache[$key]), 'Is cached?' );
}

message('Sleeping few seconds...');
sleep(3);

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler();

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
