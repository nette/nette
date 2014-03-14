<?php

/**
 * Test: Nette\Caching\Storages\FileStorage constant dependency test (continue...).
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


// Deleting dependent const

Assert::false( isset($cache[$key]) );
