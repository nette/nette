<h1>Nette\Caching\Cache priority test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Caching\Cache;*/
/*use Nette\Debug;*/

$tmpDir = dirname(__FILE__) . '/tmp';

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmpDir), RecursiveIteratorIterator::CHILD_FIRST) as $entry) // delete all files
	if ($entry->isDir()) @rmdir($entry); else @unlink($entry);

$storage = new /*Nette\Caching\*/FileStorage($tmpDir);
$cache = new Cache($storage);


echo "Writing cache...\n";
$cache->save('key1', 'value1', array(
	Cache::PRIORITY => 100,
));

$cache->save('key2', 'value2', array(
	Cache::PRIORITY => 200,
));

$cache->save('key3', 'value3', array(
	Cache::PRIORITY => 300,
));

$cache['key4'] = 'value4';


echo "Cleaning by priority...\n";
$cache->clean(array(
	Cache::PRIORITY => '200',
));

echo "Is cached key1?\n";
Debug::dump(isset($cache['key1']));

echo "Is cached key2?\n";
Debug::dump(isset($cache['key2']));

echo "Is cached key3?\n";
Debug::dump(isset($cache['key3']));

echo "Is cached key4?\n";
Debug::dump(isset($cache['key4']));
