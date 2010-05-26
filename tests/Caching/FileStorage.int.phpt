<?php

/**
 * Test: Nette\Caching\FileStorage int keys.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../NetteTest/initialize.php';



// key and data with special chars
$key = 0;
$value = range("\x00", "\xFF");

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);



$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));

dump( isset($cache[$key]), 'Is cached?' );
dump( $cache[$key], 'Cache content' );

output('Writing cache...');
$cache[$key] = $value;
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );
dump( $cache[$key] === $value, 'Is cache ok?' );

output('Removing from cache using unset()...');
unset($cache[$key]);
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );

output('Removing from cache using set NULL...');
$cache[$key] = $value;
$cache[$key] = NULL;
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Is cached? bool(FALSE)

Cache content: NULL

Writing cache...

Is cached? bool(TRUE)

Is cache ok? bool(TRUE)

Removing from cache using unset()...

Is cached? bool(FALSE)

Removing from cache using set NULL...

Is cached? bool(FALSE)
