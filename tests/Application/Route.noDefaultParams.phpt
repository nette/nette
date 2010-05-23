<?php

/**
 * Test: Nette\Application\Route with NoDefaultParams
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



$route = new Route('<presenter>/<action>/<extra>', array(
));


testRouteIn($route, '/presenter/action/12');



__halt_compiler() ?>

------EXPECT------
==> /presenter/action/12

string(9) "Presenter"

array(3) {
	"action" => string(6) "action"
	"extra" => string(2) "12"
	"test" => string(9) "testvalue"
}

NULL
