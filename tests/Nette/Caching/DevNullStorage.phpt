<?php

/**
 * Test: Nette\Caching\Storages\DevNullStorage test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\DevNullStorage;



require __DIR__ . '/../bootstrap.php';



// key and data with special chars
$key = 'nette';
$value = '"Hello World"';

$cache = new Cache(new DevNullStorage, 'myspace');


Assert::false( isset($cache[$key]), 'Is cached?' );

Assert::null( $cache[$key], 'Cache content:' );


// Writing cache...
$cache[$key] = $value;

Assert::false( isset($cache[$key]), 'Is cached?' );

Assert::false( $cache[$key] === $value, 'Is cache ok?' );


// Removing from cache using unset()...
unset($cache[$key]);

Assert::false( isset($cache[$key]), 'Is cached?' );


// Removing from cache using set NULL...
$cache[$key] = $value;
$cache[$key] = NULL;

Assert::false( isset($cache[$key]), 'Is cached?' );
