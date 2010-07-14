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
T::purge(TEMP_DIR);

$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));


$dependentFile = TEMP_DIR . '/spec.file';
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
