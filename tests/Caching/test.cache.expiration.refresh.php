<h1>Nette::Caching::Cache expiration with refresh test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Caching::Cache;*/
/*use Nette::Debug;*/

$key = 'nette';
$value = 'rulez';
$tmpDir = dirname(__FILE__) . '/tmp';

foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

$cache = new Cache(new /*Nette::Caching::*/FileStorage("$tmpDir/prefix-"));


echo "Writing cache...\n";
$cache->save($key, $value, array(
	'expire' => time() + 2,
	'refresh' => TRUE,
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
