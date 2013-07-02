<?php

/**
 * Test: Nette\Caching\Storages\FileStorage @serializationVersion dependency test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;


require __DIR__ . '/../bootstrap.php';


$key = 'nette';
$value = 'rulez';

$cache = new Cache(new FileStorage(TEMP_DIR));


class Foo
{
}


// Writing cache...
$cache->save($key, new Foo);

Assert::true( isset($cache[$key]) );
