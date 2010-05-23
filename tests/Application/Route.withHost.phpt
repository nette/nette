<?php

/**
 * Test: Nette\Application\Route with WithHost
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



$route = new Route('//<host>.<domain>/<path>', array(
	'presenter' => 'Default',
	'action' => 'default',
));


testRouteIn($route, '/abc');



__halt_compiler() ?>

------EXPECT------
==> /abc

string(7) "Default"

array(5) {
	"host" => string(7) "example"
	"domain" => string(3) "com"
	"path" => string(3) "abc"
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(19) "/abc?test=testvalue"
