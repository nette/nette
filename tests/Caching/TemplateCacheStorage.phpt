<?php

/**
 * Test: Nette\Caching\TemplateCacheStorage test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$key = 'nette';
$value = '<?php echo "Hello World" ?>';

// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);



$cache = new Cache(new /*Nette\Templates\*/TemplateCacheStorage(TEMP_DIR));


dump( isset($cache[$key]), 'Is cached?' );
dump( $cache[$key], 'Cache content' );
output('Writing cache...');
$cache[$key] = $value;

$cache->release();

dump( isset($cache[$key]), 'Is cached?' );
dump( $cache[$key], 'Cache content' );
$var = $cache[$key];

output('Test include');

// this is impossible
// $cache[$key] = NULL;

include $var['file'];

fclose($var['handle']);



__halt_compiler();

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