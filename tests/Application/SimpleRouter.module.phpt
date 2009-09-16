<?php

/**
 * Test: Nette\Application\SimpleRouter and modules.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\SimpleRouter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/SimpleRouter.inc';



$router = new SimpleRouter(array(
	'module' => 'main:sub',
));

$httpRequest = new MockHttpRequest;
$httpRequest->setQuery(array(
	'presenter' => 'myPresenter',
));

$req = $router->match($httpRequest);
dump( $req->getPresenterName() ); // "main:sub:myPresenter"

$url = $router->constructUrl($req, $httpRequest);
dump( $url ); // "http://nettephp.com/file.php?presenter=myPresenter"

$req = new /*Nette\Application\*/PresenterRequest(
	'othermodule:presenter',
	/*Nette\Web\*/HttpRequest::GET,
	array()
);
$url = $router->constructUrl($req, $httpRequest);
dump( $url ); // NULL



__halt_compiler();

------EXPECT------
string(20) "main:sub:myPresenter"

string(50) "http://nettephp.com/file.php?presenter=myPresenter"

NULL
