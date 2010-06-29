<?php

/**
 * Test: Nette\Caching\FileStorage basic usage.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../initialize.php';



// key and data with special chars
$key = '../' . implode('', range("\x00", "\x1F"));
$value = range("\x00", "\xFF");

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
T::purge(TEMP_DIR);



$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));

T::dump( isset($cache[$key]), 'Is cached?' );
T::dump( $cache[$key], 'Cache content' );

T::note('Writing cache...');
$cache[$key] = $value;
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );
T::dump( $cache[$key] === $value, 'Is cache ok?' );

T::note('Removing from cache using unset()...');
unset($cache[$key]);
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );

T::note('Removing from cache using set NULL...');
$cache[$key] = $value;
$cache[$key] = NULL;
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );



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
