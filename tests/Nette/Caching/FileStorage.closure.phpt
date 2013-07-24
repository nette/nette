<?php

/**
 * Test: Nette\Caching\Storages\FileStorage & Nette\Callback & Closure.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;


require __DIR__ . '/../bootstrap.php';


// key and data with special chars
$key = '../' . implode('', range("\x00", "\x1F"));
$value = range("\x00", "\xFF");

$cache = new Cache(new FileStorage(TEMP_DIR));

Assert::false( isset($cache[$key]) );


// Writing cache using Closure...
$res = $cache->save($key, function() use ($value) {
	return $value;
});

Assert::same( $res, $value );

Assert::same( $cache->load($key), $value );


// Removing from cache using unset()...
unset($cache[$key]);

// Writing cache using Nette\Callback...
$res = $cache->save($key, new Nette\Callback(function() use ($value) {
	return $value;
}));

Assert::same( $res, $value );

Assert::same( $cache->load($key), $value );


// Removing from cache using NULL callback...
$cache->save($key, function() {
	return NULL;
});

Assert::false( isset($cache[$key]) );
