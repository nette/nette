<?php

/**
 * Test: Nette\Caching\DummyStorage test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// key and data with special chars
$key = 'nette';
$value = '"Hello World"';

$cache = new Cache(new /*Nette\Caching\*/DummyStorage, 'myspace');


dump( isset($cache[$key]), "Is cached?" );
dump( $cache[$key], "Cache content:" );

output("Writing cache...");
$cache[$key] = $value;
$cache->release();

dump( isset($cache[$key]), "Is cached?" );
dump( $cache[$key] === $value, "Is cache ok?" );

output("Removing from cache using unset()...");
unset($cache[$key]);
$cache->release();

dump( isset($cache[$key]), "Is cached?" );

output("Removing from cache using set NULL...");
$cache[$key] = $value;
$cache[$key] = NULL;
$cache->release();

dump( isset($cache[$key]), "Is cached?" );



__halt_compiler() ?>

------EXPECT------
Is cached? bool(FALSE)

Cache content: NULL

Writing cache...

Is cached? bool(FALSE)

Is cache ok? bool(FALSE)

Removing from cache using unset()...

Is cached? bool(FALSE)

Removing from cache using set NULL...

Is cached? bool(FALSE)
