<h1>Nette::Config & ConfigAdapter_INI test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Config;*/
/*use Nette::Debug;*/


echo "Load INI\n";
$config = Config::fromFile('config1.ini');
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
Debug::dump($config->toArray());

echo "Save INI\n";
$config->display_errors = true;
$config->html_errors = false;
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');
echo "\n";
