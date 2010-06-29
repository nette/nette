<?php

/**
 * Test: Nette\Caching\TemplateCacheStorage test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache;



require __DIR__ . '/../initialize.php';



$key = 'nette';
$value = '<?php echo "Hello World" ?>';

// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
T::purge(TEMP_DIR);



$cache = new Cache(new Nette\Templates\TemplateCacheStorage(TEMP_DIR));


T::dump( isset($cache[$key]), 'Is cached?' );
T::dump( $cache[$key], 'Cache content' );
T::note('Writing cache...');
$cache[$key] = $value;

$cache->release();

T::dump( isset($cache[$key]), 'Is cached?' );
T::dump( $cache[$key], 'Cache content' );
$var = $cache[$key];

T::note('Test include');

// this is impossible
// $cache[$key] = NULL;

include $var['file'];

fclose($var['handle']);



__halt_compiler() ?>

------EXPECT------
Is cached? bool(FALSE)

Cache content: NULL

Writing cache...

Is cached? bool(TRUE)

Cache content: array(2) {
	"file" => string(%d%) "%a%nette.php"
	"handle" => resource of type(stream)
}

Test include

Hello World