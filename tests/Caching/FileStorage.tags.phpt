<?php

/**
 * Test: Nette\Caching\FileStorage tags dependency test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../NetteTest/initialize.php';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);



$storage = new Nette\Caching\FileStorage(TEMP_DIR);
$cache = new Cache($storage);


output('Writing cache...');
$cache->save('key1', 'value1', array(
	Cache::TAGS => array('one', 'two'),
));

$cache->save('key2', 'value2', array(
	Cache::TAGS => array('one', 'three'),
));

$cache->save('key3', 'value3', array(
	Cache::TAGS => array('two', 'three'),
));

$cache['key4'] = 'value4';


output('Cleaning by tags...');
$cache->clean(array(
	Cache::TAGS => 'one',
));

dump( isset($cache['key1']), 'Is cached key1?' );
dump( isset($cache['key2']), 'Is cached key2?' );
dump( isset($cache['key3']), 'Is cached key3?' );
dump( isset($cache['key4']), 'Is cached key4?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Cleaning by tags...

Is cached key1? bool(FALSE)

Is cached key2? bool(FALSE)

Is cached key3? bool(TRUE)

Is cached key4? bool(TRUE)
