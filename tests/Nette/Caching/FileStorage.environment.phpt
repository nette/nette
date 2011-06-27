<?php

/**
 * Test: Nette\Caching\Storages\FileStorage and Environment.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../bootstrap.php';



// key and data with special chars
$key = '../' . implode('', range("\x00", "\x1F"));
$value = range("\x00", "\xFF");

$cache = Environment::getCache();


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
