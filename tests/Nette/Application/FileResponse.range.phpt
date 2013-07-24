<?php

/**
 * Test: Nette\Application\Responses\FileResponse and range.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @httpCode   -
 */

use Nette\Application\Responses\FileResponse,
	Nette\Http;


require __DIR__ . '/../bootstrap.php';


$file = __FILE__;
$fileResponse = new FileResponse($file);
$origData = file_get_contents($file);

test(function() use ($fileResponse, $origData) {
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=10-20')),
		$response = new Http\Response
	);
	Assert::same( substr($origData, 10, 11), ob_get_clean() );
	Assert::same( 206, $response->getCode() );
});


test(function() use ($fileResponse, $origData) {
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=10-10')),
		new Http\Response
	);
	Assert::same( substr($origData, 10, 1), ob_get_clean() );
});


test(function() use ($fileResponse, $origData, $file) {
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=10-' . filesize($file))),
		new Http\Response
	);
	Assert::same( substr($origData, 10), ob_get_clean() );
});


test(function() use ($fileResponse, $origData) { // prefix
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=20-')),
		new Http\Response
	);
	Assert::same( substr($origData, 20), ob_get_clean() );
});


test(function() use ($fileResponse, $origData, $file) { // prefix
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=' . (filesize($file) - 1) . '-')),
		new Http\Response
	);
	Assert::same( substr($origData, -1), ob_get_clean() );
});


test(function() use ($fileResponse, $file) { // prefix
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=' . filesize($file) . '-')),
		$response = new Http\Response
	);
	Assert::same( '', ob_get_clean() );
	Assert::same( 416, $response->getCode() );
});


test(function() use ($fileResponse, $origData) { // suffix
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=-20')),
		new Http\Response
	);
	Assert::same( substr($origData, -20), ob_get_clean() );
});


test(function() use ($fileResponse, $origData, $file) { // suffix
	ob_start();
	$fileResponse->send(
		new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=-' . filesize($file))),
		new Http\Response
	);
	Assert::same( $origData, ob_get_clean() );
});
