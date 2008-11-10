<h1>Nette\Caching\Cache & DummyStorage test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Caching\Cache;*/
/*use Nette\Debug;*/

// key and data with special chars
$key = 'nette';
$value = '"Hello World"';

$cache = new Cache(new /*Nette\Caching\*/DummyStorage, 'myspace');


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

echo "Log:\n";
Debug::dump($cache->getStorage()->log);
