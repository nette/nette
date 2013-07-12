<?php

/**
 * Test: Nette\Caching\Storages\FileStorage call().
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;


require __DIR__ . '/../bootstrap.php';


class Mock
{
	function mockFunction($x, $y)
	{
		$GLOBALS['called'] = TRUE;
		return $x + $y;
	}

	function __sleep()
	{
		throw new Exception;
	}
}


$cache = new Cache(new FileStorage(TEMP_DIR));
$mock = new Mock;

$called = FALSE;
Assert::same( 55, $cache->call(array($mock, 'mockFunction'), 5, 50) );
Assert::true( $called );

$called = FALSE;
Assert::same( 55, $cache->call(array($mock, 'mockFunction'), 5, 50) );
Assert::false( $called );
