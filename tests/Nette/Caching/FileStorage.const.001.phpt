<?php

/**
 * Test: Nette\Caching\Storages\FileStorage constant dependency test.
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


define('ANY_CONST', 10);


// Writing cache...
$cache->save($key, $value, array(
	Cache::CONSTS => 'ANY_CONST',
));

Assert::true( isset($cache[$key]) );
