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

$url = new Http\UrlScript('http://nette.org/file.php');
$url->setScriptPath('/file.php');
$url->setQuery(array(
	'presenter' => 'myPresenter',
));
$httpRequest = new Http\Request($url);

$req = $router->match($httpRequest);
Assert::same( 'main:sub:myPresenter',  $req->getPresenterName() );

$url = $router->constructUrl($req, $httpRequest->url);
Assert::same( 'http://nette.org/file.php?presenter=myPresenter',  $url );

$req = new Application\Request(
	'othermodule:presenter',
	Http\Request::GET,
	array()
);
$url = $router->constructUrl($req, $httpRequest->url);
Assert::null( $url );
