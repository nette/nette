<?php

/**
 * Test: Nette\Http\Request URI.
 *
 * @author     David Grudl
 * @package    Nette\Http
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

test(function() {
	$factory = new Http\RequestFactory;
	$factory->urlFilters['path'] = array('#%20#' => '');
	$factory->urlFilters['url'] = array('#[.,)]\z#' => '');
	$request = $factory->createHttpRequest();

	Assert::same( 'GET',  $request->getMethod() );
	Assert::true( $request->isSecured() );
	Assert::same( '192.168.188.66',  $request->getRemoteAddress() );

	Assert::same( '/file.php',  $request->getUrl()->scriptPath );
	Assert::same( 'https',  $request->getUrl()->scheme );
	Assert::same( '',  $request->getUrl()->user );
	Assert::same( '',  $request->getUrl()->password );
	Assert::same( 'nette.org',  $request->getUrl()->host );
	Assert::same( 8080,  $request->getUrl()->port );
	Assert::same( '/file.php',  $request->getUrl()->path );
	Assert::same( "x param=val.&pa%\x72am=val2&param3=v a%26l%3Du%2Be",  $request->getUrl()->query );
	Assert::same( '',  $request->getUrl()->fragment );
	Assert::same( 'nette.org:8080',  $request->getUrl()->authority );
	Assert::same( 'https://nette.org:8080',  $request->getUrl()->hostUrl );
	Assert::same( 'https://nette.org:8080/',  $request->getUrl()->baseUrl );
	Assert::same( '/',  $request->getUrl()->basePath );
	Assert::same( "file.php?x param=val.&pa%\x72am=val2&param3=v a%26l%3Du%2Be",  $request->getUrl()->relativeUrl );
	Assert::same( "https://nette.org:8080/file.php?x param=val.&pa%\x72am=val2&param3=v a%26l%3Du%2Be",  $request->getUrl()->absoluteUrl );
	Assert::same( '',  $request->getUrl()->pathInfo );
});


test(function() {
	$factory = new Http\RequestFactory;
	$factory->urlFilters['path'] = array();
	$factory->urlFilters['url'] = array();
	$request = $factory->createHttpRequest();

	Assert::same( 'https',  $request->getUrl()->scheme );
	Assert::same( '',  $request->getUrl()->user );
	Assert::same( '',  $request->getUrl()->password );
	Assert::same( 'nette.org',  $request->getUrl()->host );
	Assert::same( 8080,  $request->getUrl()->port );
	Assert::same( '/file.php',  $request->getUrl()->path );
	Assert::same( 'x param=val.&pa%ram=val2&param3=v a%26l%3Du%2Be)',  $request->getUrl()->query );
	Assert::same( '',  $request->getUrl()->fragment );
	Assert::same( 'val.',  $request->getQuery('x_param') );
	Assert::same( 'val2',  $request->getQuery('pa%ram') );
	Assert::same( 'v a&l=u+e)',  $request->getQuery('param3') );
	if (!function_exists('apache_request_headers')) {
		Assert::same( 'nette.org:8080',  $request->headers['host'] );
	}
});
