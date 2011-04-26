<?php

/**
 * Test: Nette\Caching\Storages\FileStorage @serializationVersion dependency test (continue...).
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

// purge temporary directory


$cache = new Cache(new FileStorage(TEMP_DIR . '/cache'));


/**
 * @serializationVersion 123
 */
class Foo
{
}


// Changed @serializationVersion

Assert::false( isset($cache[$key]), 'Is cached?' );
