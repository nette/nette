<h1>Nette\Environment mode test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/


echo "Is console?\n";
Debug::dump(Environment::isConsole());

echo "Is production mode?\n";
Debug::dump(Environment::isProduction());

define('DEBUG_MODE', FALSE);

echo "Is debugging?\n";
Debug::dump(Environment::isDebugging());

echo "Setting mode...\n";
Environment::setMode('debug', 123);

echo "Is debugging?\n";
Debug::dump(Environment::isDebugging());
