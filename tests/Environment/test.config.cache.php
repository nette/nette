<h1>Nette::Environment config with cache test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Environment;*/

$tmpDir = dirname(__FILE__) . '/tmp';

foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

Environment::setName(Environment::PRODUCTION);


try {
	echo "Loading config #1:\n";
	Environment::loadConfig('config.ini');

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


echo "Loading config #2:\n";
Environment::setVariable('cacheBase', $tmpDir . '/');
Environment::loadConfig('config.ini');
echo "OK\n\n";


echo "Variable foo:\n";
Debug::dump(Environment::getVariable('foo'));

echo "php.ini config:\n";
Debug::dump(Environment::getConfig('set'));

echo "Database config:\n";
Debug::dump(Environment::getConfig('database'));
