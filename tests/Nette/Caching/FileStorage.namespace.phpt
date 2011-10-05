<?php

/**
 * Test: Nette\Caching\Storages\FileStorage & namespace test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Storages\FileStorage,
	Nette\Caching\Cache;



require __DIR__ . '/../bootstrap.php';



$storage = new FileStorage(TEMP_DIR);
$cacheA = new Cache($storage, 'a');
$cacheB = new Cache($storage, 'b');


// Writing cache...
$cacheA['key'] = 'hello';
$cacheB['key'] = 'world';

Assert::true( isset($cacheA['key']), 'Is cached #1?' );
Assert::true( isset($cacheB['key']), 'Is cached #2?' );
Assert::true( $cacheA['key'] === 'hello', 'Is cache ok #1?' );
Assert::true( $cacheB['key'] === 'world', 'Is cache ok #2?' );


// Removing from cache #2 using unset()...
unset($cacheB['key']);

Assert::true( isset($cacheA['key']), 'Is cached #1?' );
Assert::false( isset($cacheB['key']), 'Is cached #2?' );
