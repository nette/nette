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

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// key and data with special chars
$key = '../' . implode('', range("\x00", "\x1F"));
$value = range("\x00", "\xFF");

// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);



$cache = new Cache(new /*Nette\Caching\*/FileStorage(TEMP_DIR));

dump( isset($cache[$key]), 'Is cached?' );

output('Writing cache using Closure...');
$res = $cache->save($key, function() use ($value) {
	return $value;
});
$cache->release();

dump( $res === $value, 'Is result ok?' );
dump( $cache[$key] === $value, 'Is cache ok?' );

output('Removing from cache using unset()...');
unset($cache[$key]);
$cache->release();

output('Writing cache using Nette\Callback...');
$res = $cache->save($key, callback(function() use ($value) {
	return $value;
}));
$cache->release();

dump( $res === $value, 'Is result ok?' );
dump( $cache[$key] === $value, 'Is cache ok?' );

output('Removing from cache using NULL callback...');
$cache->save($key, function() {
	return NULL;
});
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Is cached? bool(FALSE)

Writing cache using Closure...

Is result ok? bool(TRUE)

Is cache ok? bool(TRUE)

Removing from cache using unset()...

Writing cache using Nette\Callback...

Is result ok? bool(TRUE)

Is cache ok? bool(TRUE)

Removing from cache using NULL callback...

Is cached? bool(FALSE)
