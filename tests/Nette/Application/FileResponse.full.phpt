<?php

/**
 * Test: Nette\Application\Responses\FileResponse.
 */

use Nette\Application\Responses\FileResponse;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () {
	$file = __FILE__;
	$fileResponse = new FileResponse($file);
	$origData = file_get_contents($file);

	ob_start();
	$fileResponse->send(new Http\Request(new Http\UrlScript), new Http\Response);
	Assert::same($origData, ob_get_clean());
});
