<?php

/**
 * Test: Nette\Caching\Storages\FileStorage int keys.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;


require __DIR__ . '/../bootstrap.php';


// key and data with special chars
$key = 0;
$value = range("\x00", "\xFF");

$cache = new Cache(new FileStorage(TEMP_DIR));

Assert::false( isset($cache[$key]) );

Assert::null( $cache[$key] );


// Writing cache...
$cache[$key] = $value;

Assert::true( isset($cache[$key]) );

Assert::same( $cache[$key], $value );


// Removing from cache using unset()...
unset($cache[$key]);

Assert::false( isset($cache[$key]) );


// Removing from cache using set NULL...
$cache[$key] = $value;
$cache[$key] = NULL;

Assert::false( isset($cache[$key]) );
