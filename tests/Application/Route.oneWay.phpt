<?php

/**
 * Test: Nette\Application\Route with OneWay
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



$route = new Route('<presenter>/<action>', array(
	'presenter' => 'Default',
	'action' => 'default',
), Route::ONE_WAY);


testRouteIn($route, '/presenter/action/');



__halt_compiler();

------EXPECT------
==> /presenter/action/

string(9) "Presenter"

array(2) {
	"action" => string(6) "action"
	"test" => string(9) "testvalue"
}

NULL
