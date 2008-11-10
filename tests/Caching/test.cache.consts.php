<h1>Nette\Caching\Cache constant dependency test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Caching\Cache;*/
/*use Nette\Debug;*/

$key = 'nette';
$value = 'rulez';
$tmpDir = dirname(__FILE__) . '/tmp';

foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

$cache = new Cache(new /*Nette\Caching\*/FileStorage("$tmpDir/prefix-"));


define('ANY_CONST', 10);


echo "Writing cache...\n";
$cache->save($key, $value, array(
	Cache::CONSTS => 'ANY_CONST',
));

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));
