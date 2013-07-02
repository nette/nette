<?php

/**
 * Test: Nette\Caching\Storages\FileStorage expiration test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;


require __DIR__ . '/../bootstrap.php';


$key = 'nette';
$value = 'rulez';

$cache = new Cache(new FileStorage(TEMP_DIR));


// Writing cache...
$cache->save($key, $value, array(
	Cache::EXPIRATION => time() + 3,
));


// Sleeping 1 second
sleep(1);
clearstatcache();
Assert::true( isset($cache[$key]) );


// Sleeping 3 seconds
sleep(3);
clearstatcache();
Assert::false( isset($cache[$key]) );
