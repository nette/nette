<?php

/**
 * Test: Nette\Caching\Memcached files dependency test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache,
	Nette\Caching\MemcachedStorage;



require __DIR__ . '/../initialize.php';



if (!MemcachedStorage::isAvailable()) {
	T::skip('Requires PHP extension Memcache.');
}



$key = 'nette';
$value = 'rulez';

$cache = new Cache(new MemcachedStorage('localhost'));


$dependentFile = __DIR__ . '/tmp/spec.file';
@unlink($dependentFile);

T::note('Writing cache...');
$cache->save($key, $value, array(
	Cache::FILES => array(
		__FILE__,
		$dependentFile,
	),
));
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );

T::note('Modifing dependent file');
file_put_contents($dependentFile, 'a');
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );

T::note('Writing cache...');
$cache->save($key, $value, array(
	Cache::FILES => $dependentFile,
));
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );

T::note('Modifing dependent file');
sleep(2);
file_put_contents($dependentFile, 'b');
clearstatcache();
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Is cached? TRUE

Modifing dependent file

Is cached? FALSE

Writing cache...

Is cached? TRUE

Modifing dependent file

Is cached? FALSE
