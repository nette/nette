<?php

/**
 * Test: Nette\Application\Routers\SimpleRouter invalid request.
 *
 * @author     Jan Tvrdík
 * @package    Nette\Application\Routers
 */

use Nette\Http,
	Nette\Application\Routers\SimpleRouter;


require __DIR__ . '/../bootstrap.php';




test(function() {
	$router = new SimpleRouter();
	$url = new Http\UrlScript('http://nette.org?presenter[]=foo');
	$httpRequest = new Http\Request($url);
	$req = $router->match($httpRequest);

	Assert::null( $req );
});

test(function() {
	$router = new SimpleRouter();
	$url = new Http\UrlScript('http://nette.org');
	$httpRequest = new Http\Request($url);
	$req = $router->match($httpRequest);

	Assert::null( $req );
});
