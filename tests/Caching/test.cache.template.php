<h1>Nette\Caching\Cache & TemplateCacheStorage test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Caching\Cache;*/
/*use Nette\Debug;*/

$key = 'nette';
$value = '<?php echo "Hello World" ?>';
$tmpDir = dirname(__FILE__) . '/tmp';

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmpDir), RecursiveIteratorIterator::CHILD_FIRST) as $entry) // delete all files
	if ($entry->isDir()) @rmdir($entry); else @unlink($entry);

$cache = new Cache(new /*Nette\Templates\*/TemplateCacheStorage($tmpDir));


echo "Is cached?\n";
Debug::dump(isset($cache[$key]));

echo "Cache content:\n";
Debug::dump($cache[$key]);

echo "Writing cache...\n";
$cache[$key] = $value;

$cache->release();

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));

echo "Cache content:\n";
Debug::dump($cache[$key]);
$var = $cache[$key];

echo "Test include:\n";

// this is impossible
// $cache[$key] = NULL;

include $var['file'];

fclose($var['handle']);
