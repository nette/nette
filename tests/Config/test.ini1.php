<h1>Nette::Config::Config & ConfigAdapterIni test #1</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Config::Config;*/
/*use Nette::Debug;*/

Debug::$maxDepth = NULL;

echo "Load INI\n";
$config = Config::fromFile('config1.ini');
Debug::dump($config);
echo "toArray()\n";
Debug::dump($config->toArray());

echo "Save INI\n";
$config->save('tmp/cfg.ini');
readfile('tmp/cfg.ini');
echo "\n";


echo "Save section to INI\n";
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');
echo "\n";


echo "Load section from INI\n";
$config = Config::fromFile('config1.ini', 'development', NULL);
Debug::dump($config);

echo "Save INI\n";
$config->display_errors = true;
$config->html_errors = false;
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');
echo "\n";


try {
	echo "check read-only:\n";
	$config->setReadOnly();
	$config->database->adapter = 'new value';
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
