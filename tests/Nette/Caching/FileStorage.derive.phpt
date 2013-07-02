<?php

/**
 * Test: Nette\Caching\Storages\FileStorage derive test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;


require __DIR__ . '/../bootstrap.php';


$key = 'nette';
$value = 'rulez';

$cache = new Cache(new FileStorage(TEMP_DIR), 'ns1');
$cache = $cache->derive('ns2');

$cache->save($key, $value);
Assert::same( $cache->load($key), $value );
