<?php

/**
 * Test: Nette\Application\SimpleRouter basic functions.
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
));

$uri = new Nette\Web\UriScript('http://nette.org/file.php');
$uri->setScriptPath('/file.php');
$uri->setQuery(array(
	'presenter' => 'myPresenter',
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
));
$httpRequest = new Nette\Web\HttpRequest($uri);

$req = $router->match($httpRequest);
Assert::same( 'myPresenter',  $req->getPresenterName() );
Assert::same( 'action',  $req->params['action'] );
Assert::same( '12',  $req->params['id'] );
Assert::same( 'testvalue',  $req->params['test'] );
Assert::same( 'anyvalue',  $req->params['any'] );

$url = $router->constructUrl($req, $httpRequest->uri);
Assert::same( 'http://nette.org/file.php?action=action&test=testvalue&presenter=myPresenter',  $url );
