<?php

/**
 * Test: Nette\Application\SimpleRouter with secured connection.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\SimpleRouter;



require __DIR__ . '/../NetteTest/initialize.php';

require __DIR__ . '/SimpleRouter.inc';



$router = new SimpleRouter(array(
	'id' => 12,
	'any' => 'anyvalue',
), SimpleRouter::SECURED);

$httpRequest = new MockHttpRequest;
$httpRequest->setQuery(array(
	'presenter' => 'myPresenter',
));

$req = new Nette\Application\PresenterRequest(
	'othermodule:presenter',
	Nette\Web\HttpRequest::GET,
	array()
);

$url = $router->constructUrl($req, $httpRequest);
Assert::same( "https://nette.org/file.php?presenter=othermodule%3Apresenter",  $url );
