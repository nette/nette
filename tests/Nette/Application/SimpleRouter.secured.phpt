<?php

/**
 * Test: Nette\Application\Routers\SimpleRouter with secured connection.
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Http,
	Nette\Application;



require __DIR__ . '/../bootstrap.php';



$router = new Application\Routers\SimpleRouter(array(
	'id' => 12,
	'any' => 'anyvalue',
), Application\Routers\SimpleRouter::SECURED);

$uri = new Http\UrlScript('http://nette.org/file.php');
$uri->setScriptPath('/file.php');
$uri->setQuery(array(
	'presenter' => 'myPresenter',
));
$httpRequest = new Http\Request($uri);

$req = new Application\Request(
	'othermodule:presenter',
	Http\Request::GET,
	array()
);

$url = $router->constructUrl($req, $httpRequest->uri);
Assert::same( 'https://nette.org/file.php?presenter=othermodule%3Apresenter',  $url );
