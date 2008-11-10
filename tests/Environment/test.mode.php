<h1>Nette\Environment mode test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/


echo "Is console?\n";
Debug::dump(Environment::isConsole());

echo "Is live?\n";
Debug::dump(Environment::isLive());

define('DEBUG_MODE', FALSE);

echo "Is debugging?\n";
Debug::dump(Environment::isDebugging());

echo "Setting mode...\n";
Environment::setMode('debug', 123);

echo "Is debugging?\n";
Debug::dump(Environment::isDebugging());
