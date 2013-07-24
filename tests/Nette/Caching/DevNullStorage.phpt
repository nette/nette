<?php

/**
 * Test: Nette\Caching\Storages\DevNullStorage test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\DevNullStorage;


require __DIR__ . '/../bootstrap.php';


// key and data with special chars
$key = 'nette';
$value = '"Hello World"';

$cache = new Cache(new DevNullStorage, 'myspace');


Assert::false( isset($cache[$key]) );

Assert::null( $cache[$key] );


// Writing cache...
$cache[$key] = $value;

Assert::false( isset($cache[$key]) );

Assert::notSame( $cache[$key], $value );


// Removing from cache using unset()...
unset($cache[$key]);

Assert::false( isset($cache[$key]) );


// Removing from cache using set NULL...
$cache[$key] = $value;
$cache[$key] = NULL;

Assert::false( isset($cache[$key]) );
