<?php

/**
 * Test: Nette\Application\Responses\FileResponse and range.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Application\Responses\FileResponse,
	Nette\Http;



require __DIR__ . '/../bootstrap.php';



$file = __FILE__;
$fileResponse = new FileResponse($file);
$origData = file_get_contents($file);

ob_start();
$fileResponse->send(
	new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=10-20')),
	new Http\Response
);
Assert::same( substr($origData, 10, 11), ob_get_clean() );


// prefix
ob_start();
$fileResponse->send(
	new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=20-')),
	new Http\Response
);
Assert::same( substr($origData, 20), ob_get_clean() );


// suffix
ob_start();
$fileResponse->send(
	new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=-20')),
	new Http\Response
);
Assert::same( substr($origData, -20), ob_get_clean() );
