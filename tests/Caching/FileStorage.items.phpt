<?php

/**
 * Test: Nette\Caching\FileStorage items dependency test.
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


output('Writing cache...');
$cache->save($key, $value, array(
	Cache::ITEMS => array('dependent'),
));
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );

output('Modifing dependent cached item');
$cache['dependent'] = 'hello world';
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );

output('Writing cache...');
$cache->save($key, $value, array(
	Cache::ITEMS => 'dependent',
));
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );

output('Modifing dependent cached item');
sleep(2);
$cache['dependent'] = 'hello europe';
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );

output('Writing cache...');
$cache->save($key, $value, array(
	Cache::ITEMS => 'dependent',
));
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );

output('Deleting dependent cached item');
$cache['dependent'] = NULL;
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Is cached? bool(TRUE)

Modifing dependent cached item

Is cached? bool(FALSE)

Writing cache...

Is cached? bool(TRUE)

Modifing dependent cached item

Is cached? bool(FALSE)

Writing cache...

Is cached? bool(TRUE)

Deleting dependent cached item

Is cached? bool(FALSE)
