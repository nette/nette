<?php

/**
 * Test: Nette\Caching\FileStorage constant dependency test (continue...).
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../initialize.php';



$key = 'nette';
$value = 'rulez';

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');


$cache = new Cache(new Nette\Caching\FileStorage(TEMP_DIR));


T::note('Deleting dependent const');

T::dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler() ?>

------EXPECT------
Deleting dependent const

Is cached? FALSE
