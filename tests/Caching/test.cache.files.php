<h1>Nette\Caching\Cache files dependency test</h1>

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


$dependentFile = $tmpDir . '/spec.file';
@unlink($dependentFile);

echo "Writing cache...\n";
$cache->save($key, $value, array(
	Cache::FILES => array(
		__FILE__,
		$dependentFile,
	),
));

echo "Is cached?";
Debug::dump(isset($cache[$key]));

echo "Modifing dependent file\n";
file_put_contents($dependentFile, 'a');

echo "Is cached?";
Debug::dump(isset($cache[$key]));



echo "Writing cache...\n";
$cache->save($key, $value, array(
	Cache::FILES => $dependentFile,
));

echo "Is cached?";
Debug::dump(isset($cache[$key]));

echo "Modifing dependent file\n";
sleep(2);
file_put_contents($dependentFile, 'b');
clearstatcache();

echo "Is cached?";
Debug::dump(isset($cache[$key]));
