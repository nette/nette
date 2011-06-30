<?php

/**
 * Test: Nette\Caching\Storages\FileStorage basic usage.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;



require __DIR__ . '/../bootstrap.php';



// key and data with special chars
$key = array(1, TRUE);
$value = range("\x00", "\xFF");

TestHelpers::purge(TEMP_DIR);



$cache = new Cache(new FileStorage(TEMP_DIR));

Assert::false( isset($cache[$key]), 'Is cached?' );

Assert::null( $cache[$key], 'Cache content' );


// Writing cache...
$cache[$key] = $value;
$cache->release();

Assert::true( isset($cache[$key]), 'Is cached?' );

Assert::true( $cache[$key] === $value, 'Is cache ok?' );


// Removing from cache using unset()...
unset($cache[$key]);
$cache->release();

Assert::false( isset($cache[$key]), 'Is cached?' );


// Removing from cache using set NULL...
$cache[$key] = $value;
$cache[$key] = NULL;
$cache->release();

Assert::false( isset($cache[$key]), 'Is cached?' );



// Writing cache...
$cache->save($key, $value);
$cache->release();

Assert::true( $cache->load($key) === $value, 'Is cache ok?' );
