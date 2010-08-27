<?php

/**
 * Test: Nette\Caching\FileStorage constant dependency test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../initialize.php';



$key = 'nette';
$value = 'rulez';

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
TestHelpers::purge(TEMP_DIR);


$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));


define('ANY_CONST', 10);


// Writing cache...
$cache->save($key, $value, array(
	Cache::CONSTS => 'ANY_CONST',
));
$cache->release();

Assert::true( isset($cache[$key]), 'Is cached?' );
