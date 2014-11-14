<?php

/**
 * Test: Nette\Http\Response redirect.
 */

use Nette\Http,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$response = new Http\Response;

ob_start();
$response->redirect('http://nette.org/&');
Assert::same("<h1>Redirect</h1>\n\n<p><a href=\"http://nette.org/&amp;\">Please click here to continue</a>.</p>", ob_get_clean());

if (PHP_SAPI !== 'cli') {
	Assert::contains('Location: http://nette.org/&', headers_list());
}


ob_start();
$response->redirect(' javascript:alert(1)');
Assert::same('', ob_get_clean());

if (PHP_SAPI !== 'cli') {
	Assert::contains('Location:  javascript:alert(1)', headers_list());
}


$response->setCode(200);
