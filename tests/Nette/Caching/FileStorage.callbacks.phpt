<?php

/**
 * Test: Nette\Caching\Storages\FileStorage callbacks dependency.
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

$cache = new Cache(new FileStorage(TEMP_DIR));


function dependency($val)
{
	return $val;
}


// Writing cache...
$cache->save($key, $value, array(
	Cache::CALLBACKS => array(array('dependency', 1)),
));
$cache->release();

Assert::true( isset($cache[$key]), 'Is cached?' );



// Writing cache...
$cache->save($key, $value, array(
	Cache::CALLBACKS => array(array('dependency', 0)),
));
$cache->release();

Assert::false( isset($cache[$key]), 'Is cached?' );
