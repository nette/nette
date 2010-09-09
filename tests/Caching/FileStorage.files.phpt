<?php

/**
 * Test: Nette\Caching\FileStorage files dependency test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../initialize.php';



$key = 'nette';
$value = 'rulez';

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
TestHelpers::purge(TEMP_DIR);

$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));


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
