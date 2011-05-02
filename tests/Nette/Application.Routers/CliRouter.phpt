<?php

/**
 * Test: Nette\Application\Routers\CliRouter basic usage
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Http,
	Nette\Application\Routers\CliRouter;



require __DIR__ . '/../bootstrap.php';



// php.exe app.phpc homepage:default name --verbose -user "john doe" "-pass=se cret" /wait
$_SERVER['argv'] = array(
	'app.phpc',
	'homepage:default',
	'name',
	'--verbose',
	'-user',
	'john doe',
	'-pass=se cret',
	'/wait',
);

$httpRequest = new Http\Request(new Http\UrlScript());

$router = new CliRouter(array(
	'id' => 12,
	'user' => 'anyvalue',
));
$req = $router->match($httpRequest);

Assert::same( 'homepage', $req->getPresenterName() );

Assert::same( array(
	'id' => 12,
	'user' => 'john doe',
	'action' => 'default',
	0 => 'name',
	'verbose' => TRUE,
	'pass' => 'se cret',
	'wait' => TRUE,
), $req->params );

Assert::true( $req->isMethod('cli') );


Assert::null( $router->constructUrl($req, $httpRequest->url) );
