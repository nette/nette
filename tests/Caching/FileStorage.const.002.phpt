<?php

/**
 * Test: FileStorage constant dependency test (continue...).
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$key = 'nette';
$value = 'rulez';

// temporary directory
$tempDir = dirname(__FILE__) . '/tmp';


$cache = new Cache(new /*Nette\Caching\*/FileStorage($tempDir));


message('Deleting dependent const');

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler();

------EXPECT------
Deleting dependent const

Is cached? bool(FALSE)
