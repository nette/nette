<?php

/**
 * Test: FileStorage constant dependency test.
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
$tempDir = dirname(__FILE__) . '/tmp';

NetteTestHelpers::purge($tempDir);


$cache = new Cache(new /*Nette\Caching\*/FileStorage($tempDir));


define('ANY_CONST', 10);


message('Writing cache...');
$cache->save($key, $value, array(
	Cache::CONSTS => 'ANY_CONST',
));

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler();

------EXPECT------
Writing cache...

Is cached? bool(TRUE)
