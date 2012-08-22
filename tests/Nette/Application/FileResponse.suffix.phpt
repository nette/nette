<?php

/**
 * Test: Nette\Application\Responses\FileResponse and suffix range.
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
$httpRequest = new Http\Request(new Http\UrlScript, NULL, NULL, NULL, NULL, array('range' => 'bytes=-20'));
$httpResponse = new Http\Response;

$origData = file_get_contents($file);

ob_start();
$fileResponse->send($httpRequest, $httpResponse);
$data = ob_get_clean();

Assert::same( substr($origData, -20), $data );