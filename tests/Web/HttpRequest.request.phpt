<?php

/**
 * Test: Nette\Web\HttpRequest URI.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\HttpRequest;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// Setup environment
$_SERVER = array(
	'HTTPS' => 'On',
	'HTTP_HOST' => 'nettephp.com:8080',
	'QUERY_STRING' => 'x param=val.&pa%%72am=val2&param3=v%20a%26l%3Du%2Be)',
	'REMOTE_ADDR' => '192.168.188.66',
	'REQUEST_METHOD' => 'GET',
	'REQUEST_URI' => '/file.php?x param=val.&pa%%72am=val2&param3=v%20a%26l%3Du%2Be)',
	'SCRIPT_FILENAME' => '/public_html/www/file.php',
	'SCRIPT_NAME' => '/file.php',
);

$request = new HttpRequest;
$request->addUriFilter('%20', '', PHP_URL_PATH);
$request->addUriFilter('[.,)]$');

dump( $request->getMethod() ); // 'GET'
dump( $request->isSecured() ); // TRUE
dump( $request->getRemoteAddress() ); // '192.168.188.66'

output('==> getUri');
dump( $request->getUri()->scriptPath ); // '/file.php'
dump( $request->getUri()->scheme ); // 'https'
dump( $request->getUri()->user ); // ''
dump( $request->getUri()->pass ); // ''
dump( $request->getUri()->host ); // 'nettephp.com'
dump( $request->getUri()->port ); // 8080
dump( $request->getUri()->path ); // '/file.php'
dump( $request->getUri()->query ); // "pa%\x72am=val2&param3=v a%26l%3Du%2Be&x param=val."
dump( $request->getUri()->fragment ); // ''
dump( $request->getUri()->authority ); // 'nettephp.com:8080'
dump( $request->getUri()->hostUri ); // 'https://nettephp.com:8080'
dump( $request->getUri()->baseUri ); // 'https://nettephp.com:8080/'
dump( $request->getUri()->basePath ); // '/'
dump( $request->getUri()->relativeUri ); // 'file.php'
dump( $request->getUri()->absoluteUri ); // "https://nettephp.com:8080/file.php?pa%\x72am=val2&param3=v a%26l%3Du%2Be&x param=val."
dump( $request->getUri()->pathInfo ); // ''

output('==> getOriginalUri');
dump( $request->getOriginalUri()->scheme ); // 'https'
dump( $request->getOriginalUri()->user ); // ''
dump( $request->getOriginalUri()->pass ); // ''
dump( $request->getOriginalUri()->host ); // 'nettephp.com'
dump( $request->getOriginalUri()->port ); // 8080
dump( $request->getOriginalUri()->path ); // '/file.php'
dump( $request->getOriginalUri()->query ); // 'x param=val.&pa%%72am=val2&param3=v%20a%26l%3Du%2Be)'
dump( $request->getOriginalUri()->fragment ); // ''
dump( $request->getQuery('x_param') ); // 'val.'
dump( $request->getQuery('pa%ram') ); // 'val2'
dump( $request->getQuery('param3') ); // 'v a&l=u+e'
dump( $request->getPostRaw() ); // ''
dump( $request->headers['host'] ); // 'nettephp.com:8080'



__halt_compiler();

------EXPECT------
string(3) "GET"

bool(TRUE)

string(14) "192.168.188.66"

==> getUri

string(9) "/file.php"

string(5) "https"

string(0) ""

string(0) ""

string(12) "nettephp.com"

int(8080)

string(9) "/file.php"

string(47) "x param=val.&pa%ram=val2&param3=v a%26l%3Du%2Be"

string(0) ""

string(17) "nettephp.com:8080"

string(25) "https://nettephp.com:8080"

string(26) "https://nettephp.com:8080/"

string(1) "/"

string(8) "file.php"

string(82) "https://nettephp.com:8080/file.php?x param=val.&pa%ram=val2&param3=v a%26l%3Du%2Be"

string(0) ""

==> getOriginalUri

string(5) "https"

string(0) ""

string(0) ""

string(12) "nettephp.com"

int(8080)

string(9) "/file.php"

string(52) "x param=val.&pa%%72am=val2&param3=v%20a%26l%3Du%2Be)"

string(0) ""

string(4) "val."

string(4) "val2"

string(9) "v a&l=u+e"

string(0) ""

string(17) "nettephp.com:8080"
