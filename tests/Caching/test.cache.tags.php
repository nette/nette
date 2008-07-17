<h1>Nette::Caching::Cache tags dependency test</h1>

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
	'tags' => array('one', 'two'),
));

$cache->save('key2', 'value2', array(
	'tags' => array('one', 'three'),
));

$cache->save('key3', 'value3', array(
	'tags' => array('two', 'three'),
));

$cache['key4'] = 'value4';


echo "Cleaning by tags...\n";
$cache->clean(array(
	'tags' => 'one',
));

echo "Is cached key1?\n";
Debug::dump(isset($cache['key1']));

echo "Is cached key2?\n";
Debug::dump(isset($cache['key2']));

echo "Is cached key3?\n";
Debug::dump(isset($cache['key3']));

echo "Is cached key4?\n";
Debug::dump(isset($cache['key4']));
