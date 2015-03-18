<?php

/**
 * Test: Nette\Application\Responses\FileResponse.
 */

use Nette\Application\Responses\FileResponse,
	Nette\Http,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip('Requires CGI SAPI to work with HTTP headers.');
}


test(function() {
	$file = __FILE__;
	$fileResponse = new FileResponse($file);
	$origData = file_get_contents($file);

	$fileInfo = pathinfo($file);
	$fileName = $fileInfo['filename'] . '.' . $fileInfo['extension'];

	ob_start();
	$fileResponse->send(new Http\Request(new Http\UrlScript), $response = new Http\Response);

	Assert::same( $origData, ob_get_clean() );
	Assert::same( 'attachment; filename="' . $fileName . '"; filename*=utf-8\'\'' . rawurlencode($fileName), $response->getHeader('Content-Disposition') );
});


test(function() {
	$file = __FILE__;
	$fileName = 'žluťoučký kůň.txt';
	$fileResponse = new FileResponse($file, $fileName);
	$origData = file_get_contents($file);

	ob_start();
	$fileResponse->send(new Http\Request(new Http\UrlScript), $response = new Http\Response);

	Assert::same( $origData, ob_get_clean() );
	Assert::same('attachment; filename="' . $fileName . '"; filename*=utf-8\'\'' . rawurlencode($fileName), $response->getHeader('Content-Disposition'));
});
