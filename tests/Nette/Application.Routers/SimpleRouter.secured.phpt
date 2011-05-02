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

$url = new Http\UrlScript('http://nette.org/file.php');
$url->setScriptPath('/file.php');
$url->setQuery(array(
	'presenter' => 'myPresenter',
));
$httpRequest = new Http\Request($url);

$req = new Application\Request(
	'othermodule:presenter',
	Http\Request::GET,
	array()
);

$url = $router->constructUrl($req, $httpRequest->url);
Assert::same( 'https://nette.org/file.php?presenter=othermodule%3Apresenter',  $url );
