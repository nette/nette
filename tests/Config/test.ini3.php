<h1>Nette\Config\Config & ConfigAdapterIni test #3</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Config\Config;*/
/*use Nette\Debug;*/


try {
	echo "Example 3\n";
	$config = Config::fromFile('config3.ini');
	Debug::dump($config);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


try {
	echo "Example 4\n";
	$config = Config::fromFile('config4.ini');
	Debug::dump($config);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


try {
	echo "Example 5\n";
	$config = Config::fromFile('config5.ini');
	Debug::dump($config);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
