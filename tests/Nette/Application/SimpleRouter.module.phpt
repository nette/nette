<?php

/**
 * Test: Nette\Application\Routers\SimpleRouter and modules.
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Http,
	Nette\Application;



require __DIR__ . '/../bootstrap.php';



$router = new Application\Routers\SimpleRouter(array(
	'module' => 'main:sub',
));

$uri = new Http\UrlScript('http://nette.org/file.php');
$uri->setScriptPath('/file.php');
$uri->setQuery(array(
	'presenter' => 'myPresenter',
));
$httpRequest = new Http\Request($uri);

$req = $router->match($httpRequest);
Assert::same( 'main:sub:myPresenter',  $req->getPresenterName() );

$url = $router->constructUrl($req, $httpRequest->uri);
Assert::same( 'http://nette.org/file.php?presenter=myPresenter',  $url );

$req = new Application\Request(
	'othermodule:presenter',
	Http\Request::GET,
	array()
);
$url = $router->constructUrl($req, $httpRequest->uri);
Assert::null( $url );
