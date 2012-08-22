<?php

/**
 * Test: Nette\Application\Responses\FileResponse.
 *
 * @author     Josef Kriz
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Application\Responses\FileResponse,
	Nette\Http;



require __DIR__ . '/../bootstrap.php';



$file = __FILE__;
$fileResponse = new FileResponse($file);
$httpRequest = new Http\Request(new Http\UrlScript);
$httpResponse = new Http\Response;

$origData = file_get_contents($file);

ob_start();
$fileResponse->send($httpRequest, $httpResponse);
$data = ob_get_clean();

Assert::same( $origData, $data );