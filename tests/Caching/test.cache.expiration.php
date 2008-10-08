<h1>Nette::Caching::Cache expiration test</h1>

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
	Cache::EXPIRE => time() + 2,
));


for($i = 0; $i < 4; $i++) {
	echo "Sleeping 1.1 second\n";
	usleep(1100000);
	clearstatcache();
	echo "Is cached?";
	Debug::dump(isset($cache[$key]));
}


echo "Writing cache with relative expiration...\n";
$cache->save($key, $value, array(
	Cache::EXPIRE => 2,
));


for($i = 0; $i < 4; $i++) {
	echo "Sleeping 1.1 second\n";
	usleep(1100000);
	clearstatcache();
	echo "Is cached?";
	Debug::dump(isset($cache[$key]));
}
