<?php

/**
 * Test: SimpleRouter basic functions.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/SimpleRouter.inc';

/*use Nette\Application\SimpleRouter;*/

$router = new SimpleRouter(array(
	'id' => 12,
	'any' => 'anyvalue',
));

$httpRequest = new MockHttpRequest;
$httpRequest->setQuery(array(
	'presenter' => 'myPresenter',
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
));

$req = $router->match($httpRequest);
dump( $req->getPresenterName() ); // "myPresenter"
dump( $req->params["action"] ); // "action"
dump( $req->params["id"] ); // "12"
dump( $req->params["test"] ); // "testvalue"
dump( $req->params["any"] ); // "anyvalue"

$url = $router->constructUrl($req, $httpRequest);
dump( $url ); // "http://nettephp.com/file.php?action=action&test=testvalue&presenter=myPresenter"

__halt_compiler();

------EXPECT------
string(11) "myPresenter"

string(6) "action"

string(2) "12"

string(9) "testvalue"

string(8) "anyvalue"

string(79) "http://nettephp.com/file.php?action=action&test=testvalue&presenter=myPresenter"
