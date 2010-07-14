<?php

/**
 * Test: Nette\Caching\FileStorage & Nette\Callback & Closure.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 * @phpversion 5.3
 */

use Nette\Caching\Cache,
	Nette\Environment;



require __DIR__ . '/../initialize.php';



// key and data with special chars
$key = '../' . implode('', range("\x00", "\x1F"));
$value = range("\x00", "\xFF");

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
T::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);



$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));

T::dump( isset($cache[$key]), 'Is cached?' );

T::note('Writing cache using Closure...');
$res = $cache->save($key, function() use ($value) {
	return $value;
});
$cache->release();

T::dump( $res === $value, 'Is result ok?' );
T::dump( $cache[$key] === $value, 'Is cache ok?' );

T::note('Removing from cache using unset()...');
unset($cache[$key]);
$cache->release();

T::note('Writing cache using Nette\Callback...');
$res = $cache->save($key, callback(function() use ($value) {
	return $value;
}));
$cache->release();

T::dump( $res === $value, 'Is result ok?' );
T::dump( $cache[$key] === $value, 'Is cache ok?' );

T::note('Removing from cache using NULL callback...');
$cache->save($key, function() {
	return NULL;
});
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Is cached? FALSE

Writing cache using Closure...

Is result ok? TRUE

Is cache ok? TRUE

Removing from cache using unset()...

Writing cache using %ns%Callback...

Is result ok? TRUE

Is cache ok? TRUE

Removing from cache using NULL callback...

Is cached? FALSE
