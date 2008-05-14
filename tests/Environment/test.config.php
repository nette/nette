<h1>Nette::Environment config without cache test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Environment;*/

echo "Loading config:\n";
Environment::setName(Environment::PRODUCTION);
Environment::loadConfig('config.ini', FALSE);

echo "Variable foo:\n";
Debug::dump(Environment::getVariable('foo'));

echo "Constant HELLO_WORLD:\n";
Debug::dump(constant('HELLO_WORLD'));

echo "php.ini config:\n";
Debug::dump(Environment::getConfig('set'));

echo "Database config:\n";
Debug::dump(Environment::getConfig('database'));
