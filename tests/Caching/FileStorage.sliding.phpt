<?php

/**
 * Test: Nette\Caching\FileStorage sliding expiration test.
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


output('Writing cache...');
$cache->save($key, $value, array(
	Cache::EXPIRE => time() + 2,
	Cache::SLIDING => TRUE,
));


for($i = 0; $i < 3; $i++) {
	output('Sleeping 1 second');
	sleep(1);
	clearstatcache();
	$cache->release();
	dump( isset($cache[$key]), 'Is cached?' );
}

output('Sleeping few seconds...');
sleep(3);
clearstatcache();
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Sleeping 1 second

Is cached? bool(TRUE)

Sleeping 1 second

Is cached? bool(TRUE)

Sleeping 1 second

Is cached? bool(TRUE)

Sleeping few seconds...

Is cached? bool(FALSE)
