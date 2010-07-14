<?php

/**
 * Test: Nette\Caching\DummyStorage test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../initialize.php';



// key and data with special chars
$key = 'nette';
$value = '"Hello World"';

$cache = new Cache(new Nette\Caching\DummyStorage, 'myspace');


T::dump( isset($cache[$key]), "Is cached?" );
T::dump( $cache[$key], "Cache content:" );

T::note("Writing cache...");
$cache[$key] = $value;
$cache->release();

T::dump( isset($cache[$key]), "Is cached?" );
T::dump( $cache[$key] === $value, "Is cache ok?" );

T::note("Removing from cache using unset()...");
unset($cache[$key]);
$cache->release();

T::dump( isset($cache[$key]), "Is cached?" );

T::note("Removing from cache using set NULL...");
$cache[$key] = $value;
$cache[$key] = NULL;
$cache->release();

T::dump( isset($cache[$key]), "Is cached?" );



__halt_compiler() ?>

------EXPECT------
Is cached? FALSE

Cache content: NULL

Writing cache...

Is cached? FALSE

Is cache ok? FALSE

Removing from cache using unset()...

Is cached? FALSE

Removing from cache using set NULL...

Is cached? FALSE
