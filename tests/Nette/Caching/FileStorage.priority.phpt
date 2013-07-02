<?php

/**
 * Test: Nette\Caching\Storages\FileStorage priority test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Storages\FileStorage,
	Nette\Caching\Storages\FileJournal,
	Nette\Caching\Cache;


require __DIR__ . '/../bootstrap.php';


$storage = new FileStorage(TEMP_DIR, new FileJournal(TEMP_DIR));
$cache = new Cache($storage);


// Writing cache...
$cache->save('key1', 'value1', array(
	Cache::PRIORITY => 100,
));

$cache->save('key2', 'value2', array(
	Cache::PRIORITY => 200,
));

$cache->save('key3', 'value3', array(
	Cache::PRIORITY => 300,
));

$cache['key4'] = 'value4';


// Cleaning by priority...
$cache->clean(array(
	Cache::PRIORITY => '200',
));

Assert::false( isset($cache['key1']) );
Assert::false( isset($cache['key2']) );
Assert::true( isset($cache['key3']) );
Assert::true( isset($cache['key4']) );
