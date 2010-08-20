<?php

/**
 * Test: Nette\Caching\FileStorage @serializationVersion dependency test (continue...).
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


/**
 * @serializationVersion 123
 */
class Foo
{
}


// Changed @serializationVersion

Assert::false( isset($cache[$key]), 'Is cached?' );
