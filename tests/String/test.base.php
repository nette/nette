<h1>Nette::String base method test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::String;*/


// String::startsWith
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


// String::endsWith
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


// String::webalize
echo "String::webalize('&ŽLUŤOUČKÝ KŮŇ öőôò!')\n";
Debug::dump(String::webalize('&ŽLUŤOUČKÝ KŮŇ öőôò!'));

echo "String::webalize('¼!', '!')\n";
Debug::dump(String::webalize('¼!', '!'));


// String::normalize
echo "String::normalize(...)\n";
Debug::dump(bin2hex(String::normalize("\r\nHello  \r  World \n\n")));


// String::checkUtf
echo "String::checkEncoding(...valid...)\n";
Debug::dump(String::checkEncoding('žluťoučký'));

echo "String::checkEncoding(...invalid...)\n";
Debug::dump(String::checkEncoding('žluťoučký' . chr(128)));



// String::bytes
echo "String::bytes(0.1)\n";
Debug::dump(String::bytes(0.1));

echo "String::bytes(-1024 * 1024 * 1050)\n";
Debug::dump(String::bytes(-1024 * 1024 * 1050));
