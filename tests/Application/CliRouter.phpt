<?php

/**
 * Test: CliRouter basic functions.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\CliRouter;*/
/*use Nette\Web\HttpRequest;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



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

dump( $req->getPresenterName() ); // "homepage"
dump( $req->params["action"] ); // "default"
dump( $req->params["id"] ); // "12"
dump( $req->params["user"] ); // "john doe"
dump( $req->params["pass"] ); // "se cret"
dump( $req->params["wait"] ); // TRUE
dump( $req->isMethod('cli') ); // TRUE

$url = $router->constructUrl($req, $httpRequest);
dump( $url ); // NULL



__halt_compiler();

------EXPECT------
string(8) "homepage"

string(7) "default"

int(12)

string(8) "john doe"

string(7) "se cret"

bool(TRUE)

bool(TRUE)

NULL
