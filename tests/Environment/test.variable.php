<h1>Nette::Environment variables test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Environment;*/

echo "Getting variable 'foo':\n";
Debug::dump(Environment::getVariable('foo'));



try {
	echo "Getting variable 'tempDir':\n";
	Debug::dump(Environment::getVariable('tempDir'));

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



echo "Defining constant 'APP_DIR':\n";
define('APP_DIR', '/myApp');

echo "Getting variable 'appDir':\n";
Debug::dump(Environment::getVariable('appDir'));

echo "Getting variable 'tempDir' #2:\n";
Debug::dump(Environment::getVariable('tempDir'));



echo "Setting variable 'test'...\n";
Environment::setVariable('test', '%appDir%/test');

echo "Getting variable 'test':\n";
Debug::dump(Environment::getVariable('test'));



try {
	echo "Setting circular variables...\n";
	Environment::setVariable('bar', '%foo%');
	Environment::setVariable('foo', '%foobar%');
	Environment::setVariable('foobar', '%bar%');

	echo "Getting circular variable:\n";
	Debug::dump(Environment::getVariable('bar'));

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


echo "Exporting constant 'helloWorld'...\n";
Environment::setVariable('helloWorld', 'Hello!');
Environment::exportConstant('helloWorld');

echo "Constant 'HELLO_WORLD':\n";
Debug::dump(constant('HELLO_WORLD'));
