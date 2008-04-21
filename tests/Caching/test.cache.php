<h1>Nette::Caching::Cache test</h1>

<pre>
<?php
require_once '../../Nette/Debug.php';
require_once '../../Nette/Caching/Cache.php';
require_once '../../Nette/Caching/FileCache.php';

/*use Nette::Caching::Cache;*/
/*use Nette::Debug;*/

$key = '';
$value = array();
for($i=0;$i<32;$i++) {
    $key .= chr($i);
    $value[] = chr($i) . chr(255 - $i);
}

$cache = new Cache(new /*Nette::Caching::*/FileCache(dirname(__FILE__) . '/tmp'));


echo "Is cached?\n";
Debug::dump(isset($cache[$key]));

echo "Cache content:\n";
Debug::dump($cache[$key]);

echo "Writing cache...\n";
$cache[$key] = $value;

$cache['flush'];

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));

echo "Cache is ok:\n";
Debug::dump($cache[$key] === $value);

echo "Removing from cache using unset()...\n";
unset($cache[$key]);

$cache['flush'];

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));

$cache[$key] = $value;

echo "Removing from cache using set NULL...\n";
$cache[$key] = NULL;

$cache['flush'];

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));
