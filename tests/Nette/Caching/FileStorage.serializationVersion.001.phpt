<?php

/**
 * Test: Nette\Caching\Storages\FileStorage @serializationVersion dependency test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;



require __DIR__ . '/../bootstrap.php';



$key = 'nette';
$value = 'rulez';

// purge temporary directory
TestHelpers::purge(TEMP_DIR);
// create cache directory
mkdir(TEMP_DIR . '/cache');


$cache = new Cache(new FileStorage(TEMP_DIR . '/cache'));


class Foo
{
}


// Writing cache...
$cache->save($key, new Foo);

Assert::true( isset($cache[$key]), 'Is cached?' );
