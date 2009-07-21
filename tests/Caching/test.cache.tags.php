<h1>Nette\Caching\Cache tags dependency test</h1>

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
	Cache::TAGS => array('one', 'two'),
));

$cache->save('key2', 'value2', array(
	Cache::TAGS => array('one', 'three'),
));

$cache->save('key3', 'value3', array(
	Cache::TAGS => array('two', 'three'),
));

$cache['key4'] = 'value4';


echo "Cleaning by tags...\n";
$cache->clean(array(
	Cache::TAGS => 'one',
));

echo "Is cached key1?\n";
Debug::dump(isset($cache['key1']));

echo "Is cached key2?\n";
Debug::dump(isset($cache['key2']));

echo "Is cached key3?\n";
Debug::dump(isset($cache['key3']));

echo "Is cached key4?\n";
Debug::dump(isset($cache['key4']));
