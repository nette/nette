<?php

/**
 * Test: Nette\Caching\Storages\RedisStorage tags dependency test.
 *
 * @author     David Grudl, Ondřej Slámečka
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Storages\RedisStorage,
	Nette\Caching\Cache;



require __DIR__ . '/../bootstrap.php';



if (!RedisStorage::isAvailable()) {
	TestHelpers::skip('Requires PHP extension Redis.');
}


$cache = new Cache(new RedisStorage('localhost'));


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

Assert::false( isset($cache['nette-tags-key1']), 'Is cached nette-tags-key1?' );
Assert::false( isset($cache['nette-tags-key2']), 'Is cached nette-tags-key2?' );
Assert::true( isset($cache['nette-tags-key3']), 'Is cached nette-tags-key3?' );
Assert::true( isset($cache['nette-tags-key4']), 'Is cached nette-tags-key4?' );
