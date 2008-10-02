<h1>Nette::String base method test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::String;*/


echo "String::startsWith('123', NULL)\n";
Debug::dump(String::startsWith('123', NULL));

echo "String::startsWith('123', '')\n";
Debug::dump(String::startsWith('123', ''));

echo "String::startsWith('123', '1')\n";
Debug::dump(String::startsWith('123', '1'));

echo "String::startsWith('123', '2')\n";
Debug::dump(String::startsWith('123', '2'));

echo "String::startsWith('123', '123')\n";
Debug::dump(String::startsWith('123', '123'));

echo "String::startsWith('123', '1234')\n";
Debug::dump(String::startsWith('123', '1234'));


echo "String::endsWith('123', NULL)\n";
Debug::dump(String::endsWith('123', NULL));

echo "String::endsWith('123', '')\n";
Debug::dump(String::endsWith('123', ''));

echo "String::endsWith('123', '3')\n";
Debug::dump(String::endsWith('123', '3'));

echo "String::endsWith('123', '2')\n";
Debug::dump(String::endsWith('123', '2'));

echo "String::endsWith('123', '123')\n";
Debug::dump(String::endsWith('123', '123'));

echo "String::endsWith('123', '1234')\n";
Debug::dump(String::endsWith('123', '1234'));
