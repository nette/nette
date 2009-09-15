<?php

/**
 * Test: CliRouter invald input.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\CliRouter;*/
/*use Nette\Web\HttpRequest;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$_SERVER['argv'] = 1;
$httpRequest = new HttpRequest;

$router = new CliRouter;
$req = $router->match($httpRequest);
dump( $req );



__halt_compiler();

------EXPECT------
NULL
