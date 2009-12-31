<?php

/**
 * Test: Nette\Caching\FileStorage @serializationVersion dependency test (continue...).
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
define('TEMP_DIR', dirname(__FILE__) . '/tmp');


$cache = new Cache(new /*Nette\Caching\*/FileStorage(TEMP_DIR));


/**
 * @serializationVersion 123
 */
class Foo
{
}


output('Changed @serializationVersion');

dump( isset($cache[$key]), 'Is cached?' );



__halt_compiler();

------EXPECT------
Changed @serializationVersion

Is cached? bool(FALSE)
