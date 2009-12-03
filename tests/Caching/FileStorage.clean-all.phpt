<?php

/**
 * Test: Nette\Caching\FileStorage clean with Cache::ALL
 *
 * @author     Petr Procházka
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');

$storage = new /*Nette\Caching\*/FileStorage(TEMP_DIR);
$cacheA = new Cache($storage);
$cacheB = new Cache($storage,'B');

$cacheA['test1'] = 'David';
$cacheA['test2'] = 'Grudl';
$cacheB['test1'] = 'divaD';
$cacheB['test2'] = 'ldurG';

dump(implode(' ',array(
	$cacheA['test1'],
	$cacheA['test2'],
	$cacheB['test1'],
	$cacheB['test2'],
)));

$storage->clean(array(Cache::ALL => TRUE));

dump($cacheA['test1']);
dump($cacheA['test2']);
dump($cacheB['test1']);
dump($cacheB['test2']);

__halt_compiler();

------EXPECT------
string(%i%) "David Grudl divaD ldurG"

NULL

NULL

NULL

NULL