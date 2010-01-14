<?php

/**
 * Test: Nette\Application\SimpleRouter with secured connection.
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\SimpleRouter;



require __DIR__ . '/../bootstrap.php';



$router = new SimpleRouter(array(
	'id' => 12,
	'any' => 'anyvalue',
), SimpleRouter::SECURED);

$uri = new Nette\Web\UriScript('http://nette.org/file.php');
$uri->setScriptPath('/file.php');
$uri->setQuery(array(
	'presenter' => 'myPresenter',
));
$httpRequest = new Nette\Web\HttpRequest($uri);

$req = new Nette\Application\PresenterRequest(
	'othermodule:presenter',
	Nette\Web\HttpRequest::GET,
	array()
);

$url = $router->constructUrl($req, $httpRequest->uri);
Assert::same( 'https://nette.org/file.php?presenter=othermodule%3Apresenter',  $url );
