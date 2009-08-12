<h1>Nette\Environment config test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/

echo "Loading config:\n";
Environment::setName(Environment::PRODUCTION);
Environment::loadConfig('config.ini');

echo "Variable foo:\n";
Debug::dump(Environment::getVariable('foo'));

echo "Constant HELLO_WORLD:\n";
Debug::dump(constant('HELLO_WORLD'));

echo "php.ini config:\n";
Debug::dump(Environment::getConfig('php'));

echo "Database config:\n";
Debug::dump(Environment::getConfig('database'));

echo "is production mode?\n";
Debug::dump(Environment::isProduction());
