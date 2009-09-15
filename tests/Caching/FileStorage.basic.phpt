<?php

/**
 * Test: FileStorage basic usage.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// key and data with special chars
$key = '../' . implode('', range("\x00", "\x1F"));
$value = range("\x00", "\xFF");

// temporary directory
$tempDir = dirname(__FILE__) . '/tmp';

NetteTestHelpers::purge($tempDir);



$cache = new Cache(new /*Nette\Caching\*/FileStorage($tempDir));

dump( isset($cache[$key]), 'Is cached?' );
dump( $cache[$key], 'Cache content' );

message('Writing cache...');
$cache[$key] = $value;
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );
dump( $cache[$key] === $value, 'Is cache ok?' );

message('Removing from cache using unset()...');
unset($cache[$key]);
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );

message('Removing from cache using set NULL...');
$cache[$key] = $value;
$cache[$key] = NULL;
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler();

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
