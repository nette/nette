<h1>Nette\Caching\Cache items dependency test</h1>

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
	Cache::ITEMS => array('dependent'),
));

echo "Is cached?";
Debug::dump(isset($cache[$key]));

echo "Modifing dependent cached item\n";
$cache['dependent'] = 'hello world';

echo "Is cached?";
Debug::dump(isset($cache[$key]));



echo "Writing cache...\n";
$cache->save($key, $value, array(
	Cache::ITEMS => 'dependent',
));

echo "Is cached?";
Debug::dump(isset($cache[$key]));

echo "Modifing dependent cached item\n";
sleep(2);
$cache['dependent'] = 'hello europe';

echo "Is cached?";
Debug::dump(isset($cache[$key]));



echo "Writing cache...\n";
$cache->save($key, $value, array(
	Cache::ITEMS => 'dependent',
));

echo "Is cached?";
Debug::dump(isset($cache[$key]));

echo "Deleting dependent cached item\n";
$cache['dependent'] = NULL;

echo "Is cached?";
Debug::dump(isset($cache[$key]));
