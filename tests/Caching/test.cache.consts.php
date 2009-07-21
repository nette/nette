<h1>Nette\Caching\Cache constant dependency test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Caching\Cache;*/
/*use Nette\Debug;*/

$key = 'nette';
$value = 'rulez';
$tmpDir = dirname(__FILE__) . '/tmp';

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmpDir), RecursiveIteratorIterator::CHILD_FIRST) as $entry) // delete all files
	if ($entry->isDir()) @rmdir($entry); else @unlink($entry);

$cache = new Cache(new /*Nette\Caching\*/FileStorage($tmpDir));


define('ANY_CONST', 10);


echo "Writing cache...\n";
$cache->save($key, $value, array(
	Cache::CONSTS => 'ANY_CONST',
));

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));
