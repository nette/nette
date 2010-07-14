<?php

/**
 * Test: Nette\Caching\FileStorage & namespace test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../initialize.php';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
T::purge(TEMP_DIR);


$storage = new Nette\Caching\FileStorage(TEMP_DIR);
$cacheA = new Cache($storage, 'a');
$cacheB = new Cache($storage, 'b');


T::note('Writing cache...');
$cacheA['key'] = 'hello';
$cacheB['key'] = 'world';

T::dump( isset($cacheA['key']), 'Is cached #1?' );
T::dump( isset($cacheB['key']), 'Is cached #2?' );
T::dump( $cacheA['key'] === 'hello', 'Is cache ok #1?' );
T::dump( $cacheB['key'] === 'world', 'Is cache ok #2?' );

T::note('Removing from cache #2 using unset()...');
unset($cacheB['key']);
$cacheA->release();
$cacheB->release();

T::dump( isset($cacheA['key']), 'Is cached #1?' );
T::dump( isset($cacheB['key']), 'Is cached #2?' );



__halt_compiler() ?>

------EXPECT------
Writing cache...

Is cached #1? TRUE

Is cached #2? TRUE

Is cache ok #1? TRUE

Is cache ok #2? TRUE

Removing from cache #2 using unset()...

Is cached #1? TRUE

Is cached #2? FALSE
