<?php

/**
 * Test: Nette\Caching\Storages\PhpFileStorage test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\PhpFileStorage;



require __DIR__ . '/../bootstrap.php';



$key = 'nette';
$value = '<?php echo "Hello World" ?>';

$cache = new Cache(new PhpFileStorage(TEMP_DIR));


Assert::false( isset($cache[$key]) );

Assert::null( $cache[$key] );

// Writing cache...
$cache[$key] = $value;

Assert::true( isset($cache[$key]) );

Assert::true( (bool) preg_match('#[0-9a-f]+\.php\z#', $cache[$key]['file']) );
Assert::true( is_resource($cache[$key]['handle']) );

$var = $cache[$key];

// Test include

// this is impossible
// $cache[$key] = NULL;

ob_start();
include $var['file'];
Assert::same( 'Hello World', ob_get_clean() );

fclose($var['handle']);
