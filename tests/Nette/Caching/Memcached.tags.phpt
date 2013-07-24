<?php

/**
 * Test: Nette\Caching\Storages\MemcachedStorage tags dependency test.
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
$cache->save('nette-tags-key1', 'value1', array(
	Cache::TAGS => array('one', 'two'),
));

$cache->save('nette-tags-key2', 'value2', array(
	Cache::TAGS => array('one', 'three'),
));

$cache->save('nette-tags-key3', 'value3', array(
	Cache::TAGS => array('two', 'three'),
));

$cache['nette-tags-key4'] = 'value4';


// Cleaning by tags...
$cache->clean(array(
	Cache::TAGS => 'one',
));

Assert::false( isset($cache['nette-tags-key1']) );
Assert::false( isset($cache['nette-tags-key2']) );
Assert::true( isset($cache['nette-tags-key3']) );
Assert::true( isset($cache['nette-tags-key4']) );
