<h1>Nette::Environment name test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Environment;*/


//define('ENVIRONMENT', 'lab');

echo "Name:\n";
Debug::dump(Environment::getName());


try {
    echo "Setting name:\n";
    Environment::setName('lab2');
    Debug::dump(Environment::getName());

} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
