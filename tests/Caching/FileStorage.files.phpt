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



require __DIR__ . '/../NetteTest/initialize.php';



$key = 'nette';
$value = 'rulez';

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);

$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));


$dependentFile = TEMP_DIR . '/spec.file';
@unlink($dependentFile);

output('Writing cache...');
$cache->save($key, $value, array(
	Cache::FILES => array(
		__FILE__,
		$dependentFile,
	),
));
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );

output('Modifing dependent file');
file_put_contents($dependentFile, 'a');
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );

output('Writing cache...');
$cache->save($key, $value, array(
	Cache::FILES => $dependentFile,
));
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );

output('Modifing dependent file');
sleep(2);
file_put_contents($dependentFile, 'b');
clearstatcache();
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Is cached? bool(TRUE)

Modifing dependent file

Is cached? bool(FALSE)

Writing cache...

Is cached? bool(TRUE)

Modifing dependent file

Is cached? bool(FALSE)
