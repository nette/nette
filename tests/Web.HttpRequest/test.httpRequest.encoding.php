<?php header('Content-Type: text/html; charset=utf-8'); ?>
<h1>Nette::Web::HttpRequest encoding test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Web::HttpRequest;*/

define("VALID", "\x76\xC4\x9B\xC5\xBE");
define("INVALID", "\x76\xC4\xC5\xBE");

$_GET = array(
	'test' => INVALID,
	INVALID => INVALID,
	'array' => array(INVALID => 1),
);

$_POST = array(
	'test' => INVALID,
	INVALID => INVALID,
	'array' => array(INVALID => 1),
);

$_FILES = array(
	INVALID => array(
		'name' => INVALID,
		'type' => 'text/plain',
		'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
		'error' => 0,
		'size' => 209,
	),
	'file1' => array(
		'name' => 'readme.txt',
		'type' => 'text/plain',
		'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
		'error' => 0,
		'size' => 209,
	),
);

$_COOKIE = array(
	'test' => INVALID,
	INVALID => INVALID,
	'array' => array(INVALID => 1),
);

$request = new HttpRequest;

Debug::dump($_GET);
echo 'HttpRequest::getQuery() = '; Debug::dump($request->getQuery());
echo 'HttpRequest::getPost() = '; Debug::dump($request->getPost());
echo 'HttpRequest::getFiles() = '; Debug::dump($request->getFiles());
echo 'HttpRequest::getCookies() = '; Debug::dump($request->getCookies());


echo "<h2>Encoding UTF-8</h2>\n";

$request->setEncoding('UTF-8');

echo 'HttpRequest::getQuery() = '; Debug::dump($request->getQuery());
echo 'HttpRequest::getPost() = '; Debug::dump($request->getPost());
echo 'HttpRequest::getFiles() = '; Debug::dump($request->getFiles());
echo 'HttpRequest::getCookies() = '; Debug::dump($request->getCookies());
