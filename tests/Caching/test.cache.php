<h1>Nette\Caching\Cache test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Caching\Cache;*/
/*use Nette\Debug;*/

// key and data with special chars
$key = '../';
$value = array();
for($i=0;$i<32;$i++) {
	$key .= chr($i);
	$value[] = chr($i) . chr(255 - $i);
}
$tmpDir = dirname(__FILE__) . '/tmp';

foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

$cache = new Cache(new /*Nette\Caching\*/FileStorage("$tmpDir/"));


echo "Is cached?\n";
Debug::dump(isset($cache[$key]));

echo "Cache content:\n";
Debug::dump($cache[$key]);

echo "Writing cache...\n";
$cache[$key] = $value;

$cache->release();

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));

echo "Is cache ok?\n";
Debug::dump($cache[$key] === $value);

echo "Removing from cache using unset()...\n";
unset($cache[$key]);

$cache->release();

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));

$cache[$key] = $value;

echo "Removing from cache using set NULL...\n";
$cache[$key] = NULL;

$cache->release();

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));
