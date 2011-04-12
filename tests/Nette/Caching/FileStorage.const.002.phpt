<?php

/**
 * Test: Nette\Caching\FileStorage constant dependency test (continue...).
 *
 * @author     David Grudl
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../bootstrap.php';



$key = 'nette';
$value = 'rulez';

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');


$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));


// Deleting dependent const

Assert::false( isset($cache[$key]), 'Is cached?' );
