<?php

/**
 * Test: Nette\Application\CliRouter basic usage
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\CliRouter,
	Nette\Web\HttpRequest;



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

$httpRequest = new HttpRequest;

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


Assert::null( $router->constructUrl($req, $httpRequest) );
