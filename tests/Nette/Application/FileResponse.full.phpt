<?php

/**
 * Test: Nette\Application\Responses\FileResponse.
 *
 * @author     Josef Kriz
 * @package    Nette\Config
 */

use Nette\Application\Responses\FileResponse,
	Nette\Http;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$file = __FILE__;
	$fileResponse = new FileResponse($file);
	$origData = file_get_contents($file);

	ob_start();
	$fileResponse->send(new Http\Request(new Http\UrlScript), new Http\Response);
	Assert::same( $origData, ob_get_clean() );
});
