<?php

/**
 * Test: Nette\Application\Routers\CliRouter invalid argument
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Http,
	Nette\Application\Routers\CliRouter;



require __DIR__ . '/../bootstrap.php';



$_SERVER['argv'] = 1;
$httpRequest = new Http\Request(new Http\UrlScript());

$router = new CliRouter;
Assert::null( $router->match($httpRequest) );
