<?php

/**
 * Test: Nette\Caching\Storages\FileStorage constant dependency test (continue...).
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

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');


$cache = new Cache(new FileStorage(TEMP_DIR));


// Deleting dependent const

Assert::false( isset($cache[$key]), 'Is cached?' );
