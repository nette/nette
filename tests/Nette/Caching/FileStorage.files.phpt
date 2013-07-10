<?php

/**
 * Test: Nette\Caching\Storages\FileStorage files dependency test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
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

Assert::true( isset($cache[$key]) );


// Modifing dependent file
sleep(2);
file_put_contents($dependentFile, 'a');
clearstatcache();

Assert::false( isset($cache[$key]) );


// Writing cache...
$cache->save($key, $value, array(
	Cache::FILES => $dependentFile,
));

Assert::true( isset($cache[$key]) );


// Modifing dependent file
sleep(2);
file_put_contents($dependentFile, 'b');
clearstatcache();

Assert::false( isset($cache[$key]) );
