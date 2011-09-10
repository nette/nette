<?php

/**
 * Test: Nette\Caching\Storages\FileStorage expiration test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
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
$cache->release();
Assert::true( isset($cache[$key]), 'Is cached?' );



// Sleeping 3 seconds
sleep(3);
clearstatcache();
$cache->release();
Assert::false( isset($cache[$key]), 'Is cached?' );
