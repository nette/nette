<?php

/**
 * Test: Nette\Application\CliRouter basic usage
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
dump( $req->params );
dump( $req->isMethod('cli') ); // TRUE

dump( $router->constructUrl($req, $httpRequest) ); // NULL



__halt_compiler() ?>

------EXPECT------
string(8) "homepage"

array(7) {
	"id" => int(12)
	"user" => string(8) "john doe"
	"action" => string(7) "default"
	0 => string(4) "name"
	"verbose" => bool(TRUE)
	"pass" => string(7) "se cret"
	"wait" => bool(TRUE)
}

bool(TRUE)

NULL
