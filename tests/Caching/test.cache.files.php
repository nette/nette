<h1>Nette::Caching::Cache files dependency test</h1>

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


$dependentFile = $tmpDir . '/spec.file';
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

echo "Is cached?";
Debug::dump(isset($cache[$key]));
