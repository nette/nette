<?php

/**
 * Test: Nette\Application\SimpleRouter and modules.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\SimpleRouter;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/SimpleRouter.inc';



$router = new SimpleRouter(array(
	'module' => 'main:sub',
));

$httpRequest = new MockHttpRequest;
$httpRequest->setQuery(array(
	'presenter' => 'myPresenter',
));

$req = $router->match($httpRequest);
Assert::same( 'main:sub:myPresenter',  $req->getPresenterName() );

$url = $router->constructUrl($req, $httpRequest);
Assert::same( 'http://nette.org/file.php?presenter=myPresenter',  $url );

$req = new Nette\Application\PresenterRequest(
	'othermodule:presenter',
	Nette\Web\HttpRequest::GET,
	array()
);
$url = $router->constructUrl($req, $httpRequest);
Assert::null( $url );
