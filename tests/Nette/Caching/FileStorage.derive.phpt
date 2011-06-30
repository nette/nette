<?php

/**
 * Test: Nette\Caching\Storages\FileStorage derive test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;



require __DIR__ . '/../bootstrap.php';



$key = 'nette';
$value = 'rulez';

TestHelpers::purge(TEMP_DIR);


$cache = new Cache(new FileStorage(TEMP_DIR), 'ns1');
$cache = $cache->derive('ns2');

$cache->save($key, $value);
$cache->release();
Assert::same( $cache[$key], $value );
