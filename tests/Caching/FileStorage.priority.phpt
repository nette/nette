<?php

/**
 * Test: Nette\Caching\FileStorage priority test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);


$storage = new /*Nette\Caching\*/FileStorage(TEMP_DIR);
$cache = new Cache($storage);


output('Writing cache...');
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


output('Cleaning by priority...');
$cache->clean(array(
	Cache::PRIORITY => '200',
));

dump( isset($cache['key1']), 'Is cached key1?' );
dump( isset($cache['key2']), 'Is cached key2?' );
dump( isset($cache['key3']), 'Is cached key3?' );
dump( isset($cache['key4']), 'Is cached key4?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Cleaning by priority...

Is cached key1? bool(FALSE)

Is cached key2? bool(FALSE)

Is cached key3? bool(TRUE)

Is cached key4? bool(TRUE)
