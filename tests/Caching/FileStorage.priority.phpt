<?php

/**
 * Test: Nette\Caching\FileStorage priority test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../bootstrap.php';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
TestHelpers::purge(TEMP_DIR);


$context = new Nette\Context;
$context->addService('Nette\\Caching\\ICacheJournal', new Nette\Caching\FileJournal(TEMP_DIR));
$storage = new Nette\Caching\FileStorage(TEMP_DIR, $context);
$cache = new Cache($storage);


// Writing cache...
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


// Cleaning by priority...
$cache->clean(array(
	Cache::PRIORITY => '200',
));

Assert::false( isset($cache['key1']), 'Is cached key1?' );
Assert::false( isset($cache['key2']), 'Is cached key2?' );
Assert::true( isset($cache['key3']), 'Is cached key3?' );
Assert::true( isset($cache['key4']), 'Is cached key4?' );
