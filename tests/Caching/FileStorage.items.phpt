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



require __DIR__ . '/../initialize.php';



$key = 'nette';
$value = 'rulez';

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
T::purge(TEMP_DIR);


$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));


T::note('Writing cache...');
$cache->save($key, $value, array(
	Cache::ITEMS => array('dependent'),
));
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );

T::note('Modifing dependent cached item');
$cache['dependent'] = 'hello world';
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );

T::note('Writing cache...');
$cache->save($key, $value, array(
	Cache::ITEMS => 'dependent',
));
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );

T::note('Modifing dependent cached item');
sleep(2);
$cache['dependent'] = 'hello europe';
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );

T::note('Writing cache...');
$cache->save($key, $value, array(
	Cache::ITEMS => 'dependent',
));
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );

T::note('Deleting dependent cached item');
$cache['dependent'] = NULL;
$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Is cached? TRUE

Modifing dependent cached item

Is cached? FALSE

Writing cache...

Is cached? TRUE

Modifing dependent cached item

Is cached? FALSE

Writing cache...

Is cached? TRUE

Deleting dependent cached item

Is cached? FALSE
