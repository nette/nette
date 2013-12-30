<?php

/**
 * Test: Nette\Caching\Storages\FileStorage @serializationVersion dependency test (continue...).
 *
 * @author     David Grudl
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$key = 'nette';
$value = 'rulez';


$cache = new Cache(new FileStorage(TEMP_DIR));


/**
 * @serializationVersion 123
 */
class Foo
{
}


// Changed @serializationVersion

Assert::false( isset($cache[$key]) );
