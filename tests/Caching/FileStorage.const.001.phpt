<?php

/**
 * Test: Nette\Caching\FileStorage constant dependency test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$key = 'nette';
$value = 'rulez';

// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);


$cache = new Cache(new /*Nette\Caching\*/FileStorage(TEMP_DIR));


define('ANY_CONST', 10);


output('Writing cache...');
$cache->save($key, $value, array(
	Cache::CONSTS => 'ANY_CONST',
));
$cache->release();

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler();

------EXPECT------
Writing cache...

Is cached? bool(TRUE)
