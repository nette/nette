<?php

/**
 * Test: FileStorage expiration test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$key = 'nette';
$value = 'rulez';

// temporary directory
$tempDir = dirname(__FILE__) . '/tmp';

NetteTestHelpers::purge($tempDir);

$cache = new Cache(new /*Nette\Caching\*/FileStorage($tempDir));


message('Writing cache...');
$cache->save($key, $value, array(
	Cache::EXPIRE => time() + 2,
));


for($i = 0; $i < 4; $i++) {
	message('Sleeping 1.2 second');
	usleep(1100000);
	clearstatcache();
	dump( isset($cache[$key]), 'Is cached?' );
}


message('Writing cache with relative expiration...');
$cache->save($key, $value, array(
	Cache::EXPIRE => 2,
));


for($i = 0; $i < 4; $i++) {
	message('Sleeping 1.2 second');
	usleep(1100000);
	clearstatcache();
	dump( isset($cache[$key]), 'Is cached?' );
}



__halt_compiler();

------EXPECT------
Writing cache...

Sleeping 1.2 second

Is cached? bool(TRUE)

Sleeping 1.2 second

Is cached? bool(TRUE)

Sleeping 1.2 second

Is cached? bool(FALSE)

Sleeping 1.2 second

Is cached? bool(FALSE)

Writing cache with relative expiration...

Sleeping 1.2 second

Is cached? bool(TRUE)

Sleeping 1.2 second

Is cached? bool(TRUE)

Sleeping 1.2 second

Is cached? bool(FALSE)

Sleeping 1.2 second

Is cached? bool(FALSE)
