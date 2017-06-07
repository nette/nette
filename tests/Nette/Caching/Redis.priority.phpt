<?php

/**
 * Test: Nette\Caching\Storages\RedisStorage priority test.
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

Assert::false( isset($cache['nette-priority-key1']), 'Is cached nette-priority-key1?' );
Assert::false( isset($cache['nette-priority-key2']), 'Is cached nette-priority-key2?' );
Assert::true( isset($cache['nette-priority-key3']), 'Is cached nette-priority-key3?' );
Assert::true( isset($cache['nette-priority-key4']), 'Is cached nette-priority-key4?' );
