<?php

/**
 * Test: Nette\Http\Request URI.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http;



require __DIR__ . '/../bootstrap.php';



// Setup environment
$_SERVER = array(
	'HTTPS' => 'On',
	'HTTP_HOST' => 'nette.org:8080',
	'QUERY_STRING' => 'x param=val.&pa%%72am=val2&param3=v%20a%26l%3Du%2Be)',
	'REMOTE_ADDR' => '192.168.188.66',
	'REQUEST_METHOD' => 'GET',
	'REQUEST_URI' => '/file.php?x param=val.&pa%%72am=val2&param3=v%20a%26l%3Du%2Be)',
	'SCRIPT_FILENAME' => '/public_html/www/file.php',
	'SCRIPT_NAME' => '/file.php',
);

$factory = new Http\RequestFactory;
$factory->uriFilters['path'] = array('#%20#' => '');
$factory->uriFilters['uri'] = array('#[.,)]$#' => '');
$request = $factory->createHttpRequest();

Assert::same( 'GET',  $request->getMethod() );
Assert::true( $request->isSecured() );
Assert::same( '192.168.188.66',  $request->getRemoteAddress() );

Assert::same( '/file.php',  $request->getUri()->scriptPath );
Assert::same( 'https',  $request->getUri()->scheme );
Assert::same( '',  $request->getUri()->user );
Assert::same( '',  $request->getUri()->password );
Assert::same( 'nette.org',  $request->getUri()->host );
Assert::same( 8080,  $request->getUri()->port );
Assert::same( '/file.php',  $request->getUri()->path );
Assert::same( "x param=val.&pa%\x72am=val2&param3=v a%26l%3Du%2Be",  $request->getUri()->query );
Assert::same( '',  $request->getUri()->fragment );
Assert::same( 'nette.org:8080',  $request->getUri()->authority );
Assert::same( 'https://nette.org:8080',  $request->getUri()->hostUri );
Assert::same( 'https://nette.org:8080/',  $request->getUri()->baseUri );
Assert::same( '/',  $request->getUri()->basePath );
Assert::same( "file.php?x param=val.&pa%\x72am=val2&param3=v a%26l%3Du%2Be",  $request->getUri()->relativeUri );
Assert::same( "https://nette.org:8080/file.php?x param=val.&pa%\x72am=val2&param3=v a%26l%3Du%2Be",  $request->getUri()->absoluteUri );
Assert::same( '',  $request->getUri()->pathInfo );


$factory = new Http\RequestFactory;
$factory->uriFilters['path'] = array();
$factory->uriFilters['uri'] = array();
$request = $factory->createHttpRequest();

Assert::same( 'https',  $request->getUri()->scheme );
Assert::same( '',  $request->getUri()->user );
Assert::same( '',  $request->getUri()->password );
Assert::same( 'nette.org',  $request->getUri()->host );
Assert::same( 8080,  $request->getUri()->port );
Assert::same( '/file.php',  $request->getUri()->path );
Assert::same( 'x param=val.&pa%ram=val2&param3=v a%26l%3Du%2Be)',  $request->getUri()->query );
Assert::same( '',  $request->getUri()->fragment );
Assert::same( 'val.',  $request->getQuery('x_param') );
Assert::same( 'val2',  $request->getQuery('pa%ram') );
Assert::same( 'v a&l=u+e)',  $request->getQuery('param3') );
Assert::same( 'nette.org:8080',  $request->headers['host'] );
