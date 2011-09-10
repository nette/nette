<?php

/**
 * Test: Nette\Caching\Storages\FileStorage files dependency test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;



require __DIR__ . '/../bootstrap.php';



$key = 'nette';
$value = 'rulez';

$cache = new Cache(new FileStorage(TEMP_DIR));


$dependentFile = TEMP_DIR . '/spec.file';
@unlink($dependentFile);

// Writing cache...
$cache->save($key, $value, array(
	Cache::FILES => array(
		__FILE__,
		$dependentFile,
	),
));
$cache->release();

Assert::true( isset($cache[$key]), 'Is cached?' );


// Modifing dependent file
file_put_contents($dependentFile, 'a');
$cache->release();

Assert::false( isset($cache[$key]), 'Is cached?' );


// Writing cache...
$cache->save($key, $value, array(
	Cache::FILES => $dependentFile,
));
$cache->release();

Assert::true( isset($cache[$key]), 'Is cached?' );


// Modifing dependent file
sleep(2);
file_put_contents($dependentFile, 'b');
clearstatcache();
$cache->release();

Assert::false( isset($cache[$key]), 'Is cached?' );
