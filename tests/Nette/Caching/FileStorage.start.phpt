<?php

/**
 * Test: Nette\Caching\Storages\FileStorage start().
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;



require __DIR__ . '/../bootstrap.php';



$cache = new Cache(new FileStorage(TEMP_DIR));


ob_start();
$block = $cache->start('key');
Assert::true( $block instanceof Nette\Caching\OutputHelper );
echo 'Hello';
$block->end();
Assert::equal( 'Hello', ob_get_clean() );


Assert::equal( 'Hello', $cache->load('key') );


ob_start();
Assert::null( $cache->start('key') );
Assert::equal( 'Hello', ob_get_clean() );
