<h1>Nette\Caching\Cache callbacks dependency test</h1>

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


function dependency($val)
{
	return $val;
}


echo "Writing cache...\n";
$cache->save($key, $value, array(
	Cache::CALLBACKS => array(array('dependency', 1)),
));

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));


echo "Writing cache...\n";
$cache->save($key, $value, array(
	Cache::CALLBACKS => array(array('dependency', 0)),
));

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));
