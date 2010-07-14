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
T::purge(TEMP_DIR);


$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));


define('ANY_CONST', 10);


T::note('Writing cache...');
$cache->save($key, $value, array(
	Cache::CONSTS => 'ANY_CONST',
));
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Is cached? TRUE
