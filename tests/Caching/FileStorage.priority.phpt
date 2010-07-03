<?php

/**
 * Test: Nette\Caching\FileStorage priority test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../initialize.php';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
Nette\Environment::setVariable('tempDir', TEMP_DIR);
T::purge(TEMP_DIR);


$storage = new Nette\Caching\FileStorage(TEMP_DIR);
$cache = new Cache($storage);


T::note('Writing cache...');
$cache->save('key1', 'value1', array(
	Cache::PRIORITY => 100,
));

$cache->save('key2', 'value2', array(
	Cache::PRIORITY => 200,
));

$cache->save('key3', 'value3', array(
	Cache::PRIORITY => 300,
));

$cache['key4'] = 'value4';


T::note('Cleaning by priority...');
$cache->clean(array(
	Cache::PRIORITY => '200',
));

T::dump( isset($cache['key1']), 'Is cached key1?' );
T::dump( isset($cache['key2']), 'Is cached key2?' );
T::dump( isset($cache['key3']), 'Is cached key3?' );
T::dump( isset($cache['key4']), 'Is cached key4?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Cleaning by priority...

Is cached key1? FALSE

Is cached key2? FALSE

Is cached key3? TRUE

Is cached key4? TRUE
