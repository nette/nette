<h1>Nette\Caching\Cache sliding expiration test</h1>

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


echo "Writing cache...\n";
$cache->save($key, $value, array(
	Cache::EXPIRE => time() + 2,
	Cache::SLIDING => TRUE,
));


for($i = 0; $i < 3; $i++) {
	echo "Sleeping 1 second\n";
	sleep(1);
	clearstatcache();
	echo "Is cached?";
	Debug::dump(isset($cache[$key]));
}

echo "Sleeping few seconds...\n";
sleep(3);
clearstatcache();

echo "Is cached?";
Debug::dump(isset($cache[$key]));
