<?php

/**
 * Test: Nette\Caching\Storages\FileStorage & namespace test.
 */

use Nette\Caching\Storages\FileStorage,
	Nette\Caching\Cache,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$storage = new FileStorage(TEMP_DIR);
$cacheA = new Cache($storage, 'a');
$cacheB = new Cache($storage, 'b');


// Writing cache...
$cacheA['key'] = 'hello';
$cacheB['key'] = 'world';

Assert::true( isset($cacheA['key']) );
Assert::true( isset($cacheB['key']) );
Assert::same( $cacheA['key'], 'hello' );
Assert::same( $cacheB['key'], 'world' );


// Removing from cache #2 using unset()...
unset($cacheB['key']);

Assert::true( isset($cacheA['key']) );
Assert::false( isset($cacheB['key']) );
