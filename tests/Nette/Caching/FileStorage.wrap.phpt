<?php

/**
 * Test: Nette\Caching\Storages\FileStorage wrap().
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;


require __DIR__ . '/../bootstrap.php';


function mockFunction($x, $y)
{
	$GLOBALS['called'] = TRUE;
	return $x + $y;
}


$cache = new Cache(new FileStorage(TEMP_DIR));

$called = FALSE;
Assert::same( 55, call_user_func($cache->wrap('mockFunction'), 5, 50) );
Assert::true( $called );

$called = FALSE;
Assert::same( 55, call_user_func($cache->wrap('mockFunction'), 5, 50) );
Assert::false( $called );
