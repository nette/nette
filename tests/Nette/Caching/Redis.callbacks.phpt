<?php

/**
 * Test: Nette\Caching\Storages\RedisStorage expiration test.
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



$key = 'nette-expiration-key';
$value = 'rulez';

$cache = new Cache(new RedisStorage('localhost'));

function dependency($val)
{
	return $val;
}


// Writing cache...
$cache->save($key, $value, array(
	Cache::CALLBACKS => array(array('dependency', 1)),
));

Assert::true( isset($cache[$key]), 'Is cached?' );



// Writing cache...
$cache->save($key, $value, array(
	Cache::CALLBACKS => array(array('dependency', 0)),
));

Assert::false( isset($cache[$key]), 'Is cached?' );
