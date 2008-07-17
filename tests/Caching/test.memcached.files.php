<h1>Nette::Caching::Cache & Memcached files dependency test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Caching::Cache;*/
/*use Nette::Debug;*/

$key = 'nette';
$value = 'rulez';

$cache = new Cache(new /*Nette::Caching::*/MemcachedStorage('localhost'));


$dependentFile = dirname(__FILE__) . '/tmp/spec.file';
@unlink($dependentFile);

echo "Writing cache...\n";
$cache->save($key, $value, array(
	'files' => array(
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
	'files' => $dependentFile,
));

echo "Is cached?";
Debug::dump(isset($cache[$key]));

echo "Modifing dependent file\n";
sleep(2);
file_put_contents($dependentFile, 'b');
clearstatcache();

echo "Is cached?";
Debug::dump(isset($cache[$key]));
