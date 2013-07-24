<?php

/**
 * Test: Nette\Caching\Storages\MemcachedStorage priority test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Storages\MemcachedStorage,
	Nette\Caching\Storages\FileJournal,
	Nette\Caching\Cache;


require __DIR__ . '/../bootstrap.php';


if (!MemcachedStorage::isAvailable()) {
	Tester\Environment::skip('Requires PHP extension Memcache.');
}


$storage = new MemcachedStorage('localhost', 11211, '', new FileJournal(TEMP_DIR));
$cache = new Cache($storage);


// Writing cache...
$cache->save('nette-priority-key1', 'value1', array(
	Cache::PRIORITY => 100,
));

$cache->save('nette-priority-key2', 'value2', array(
	Cache::PRIORITY => 200,
));

$cache->save('nette-priority-key3', 'value3', array(
	Cache::PRIORITY => 300,
));

$cache['nette-priority-key4'] = 'value4';


// Cleaning by priority...
$cache->clean(array(
	Cache::PRIORITY => '200',
));

Assert::false( isset($cache['nette-priority-key1']) );
Assert::false( isset($cache['nette-priority-key2']) );
Assert::true( isset($cache['nette-priority-key3']) );
Assert::true( isset($cache['nette-priority-key4']) );
