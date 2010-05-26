<?php

/**
 * Test: Nette\Caching\FileStorage callbacks dependency.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../NetteTest/initialize.php';



$key = 'nette';
$value = 'rulez';

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);



$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));


function dependency($val)
{
	return $val;
}


output('Writing cache...');
$cache->save($key, $value, array(
	Cache::CALLBACKS => array(array('dependency', 1)),
));
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );


output('Writing cache...');
$cache->save($key, $value, array(
	Cache::CALLBACKS => array(array('dependency', 0)),
));
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Is cached? bool(TRUE)

Writing cache...

Is cached? bool(FALSE)
