<h1>Nette::Caching::Cache priority test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Caching::Cache;*/
/*use Nette::Debug;*/

$tmpDir = dirname(__FILE__) . '/tmp';

foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

$storage = new /*Nette::Caching::*/FileStorage("$tmpDir/prefix-");
$cache = new Cache($storage);


echo "Writing cache...\n";
$cache->save('key1', 'value1', array(
	'priority' => 100,
));

$cache->save('key2', 'value2', array(
	'priority' => 200,
));

$cache->save('key3', 'value3', array(
	'priority' => 300,
));

$cache['key4'] = 'value4';


echo "Cleaning by priority...\n";
$cache->clean(array(
	'priority' => '200',
));

echo "Is cached key1?\n";
Debug::dump(isset($cache['key1']));

echo "Is cached key2?\n";
Debug::dump(isset($cache['key2']));

echo "Is cached key3?\n";
Debug::dump(isset($cache['key3']));

echo "Is cached key4?\n";
Debug::dump(isset($cache['key4']));
