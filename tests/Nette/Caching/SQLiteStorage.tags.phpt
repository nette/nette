<?php

/**
 * Test: Nette\Caching\Storages\SQLiteStorage tags dependency test.
 *
 * @author     David Grudl
 */

use Nette\Caching\Storages\SQLiteStorage,
	Nette\Caching\Cache,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


if (!extension_loaded('pdo_sqlite')) {
	Tester\Environment::skip('Requires PHP extension pdo_sqlite.');
}


$cache = new Cache(new SQLiteStorage(TEMP_DIR . '/db.db3'));


// Writing cache...
$cache->save('key1', 'value1', array(
	Cache::TAGS => array('one', 'two'),
));

$cache->save('key2', 'value2', array(
	Cache::TAGS => array('one', 'three'),
));

$cache->save('key3', 'value3', array(
	Cache::TAGS => array('two', 'three'),
));

$cache['key4'] = 'value4';


// Cleaning by tags...
$cache->clean(array(
	Cache::TAGS => array('one', 'xx'),
));

Assert::false( isset($cache['key1']) );
Assert::false( isset($cache['key2']) );
Assert::true( isset($cache['key3']) );
Assert::true( isset($cache['key4']) );
