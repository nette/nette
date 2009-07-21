<h1>Nette\Caching\Cache & namespace test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Caching\Cache;*/
/*use Nette\Debug;*/

$tmpDir = dirname(__FILE__) . '/tmp';

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmpDir), RecursiveIteratorIterator::CHILD_FIRST) as $entry) // delete all files
	if ($entry->isDir()) @rmdir($entry); else @unlink($entry);

$storage = new /*Nette\Caching\*/FileStorage($tmpDir);
$cacheA = new Cache($storage, 'a');
$cacheB = new Cache($storage, 'b');


echo "Writing cache...\n";
$cacheA['key'] = 'hello';
$cacheB['key'] = 'world';

echo "Is cached #1?\n";
Debug::dump(isset($cacheA['key']));

echo "Is cached #2?\n";
Debug::dump(isset($cacheB['key']));

echo "Is cache ok #1?\n";
Debug::dump($cacheA['key'] === 'hello');

echo "Is cache ok #2?\n";
Debug::dump($cacheB['key'] === 'world');

echo "Removing from cache #2 using unset()...\n";
unset($cacheB['key']);

$cacheA->release();
$cacheB->release();

echo "Is cached #1?\n";
Debug::dump(isset($cacheA['key']));

echo "Is cached #2?\n";
Debug::dump(isset($cacheB['key']));
