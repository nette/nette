<h1>Nette::Config & ConfigAdapter_INI test #2</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Config;*/
/*use Nette::Debug;*/

Debug::$maxDepth = NULL;


echo "Load INI\n";
$config = Config::fromFile('config2.ini');
Debug::dump($config);

echo "Save INI\n";
$config->save('tmp/cfg.ini');
readfile('tmp/cfg.ini');
echo "\n";


echo "Save section to INI\n";
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');
echo "\n";


echo "Load section from INI\n";
$config = Config::fromFile('config2.ini', 'development', NULL);
Debug::dump($config);

echo "Save INI\n";
$config->display_errors = true;
$config->html_errors = false;
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');
echo "\n";
