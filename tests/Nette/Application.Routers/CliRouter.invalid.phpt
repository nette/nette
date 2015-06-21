<?php

/**
 * Test: Nette\Application\Routers\CliRouter invalid argument
 */

use Nette\Http;
use Nette\Application\Routers\CliRouter;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_SERVER['argv'] = 1;
$httpRequest = new Http\Request(new Http\UrlScript());

$router = new CliRouter;
Assert::null($router->match($httpRequest));
