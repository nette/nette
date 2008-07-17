<h1>Nette::Caching::Cache & TemplateStorage test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Caching::Cache;*/
/*use Nette::Debug;*/

$key = 'nette';
$value = '<?php echo "Hello World" ?>';
$tmpDir = dirname(__FILE__) . '/tmp';

foreach (glob("$tmpDir/*.*") as $file) unlink($file); // delete all files

$cache = new Cache(new /*Nette::Templates::*/TemplateStorage("$tmpDir/prefix-"));


echo "Is cached?\n";
Debug::dump(isset($cache[$key]));

echo "Cache content:\n";
Debug::dump($cache[$key]);

echo "Writing cache...\n";
$cache[$key] = $value;

$cache->release();

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));

echo "Cache content:\n";
Debug::dump($cache[$key]);
$var = $cache[$key];

echo "Test include:\n";

// this is impossible
// $cache[$key] = NULL;

include $var['file'];

fclose($var['handle']);
