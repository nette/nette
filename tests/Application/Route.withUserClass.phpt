<?php

/**
 * Test: Nette\Application\Route with WithUserClass
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



Route::addStyle('#numeric');
Route::setStyleProperty('#numeric', Route::PATTERN, '\d{1,3}');

$route = new Route('<presenter>/<id #numeric>', array());

testRouteIn($route, '/presenter/12/');

testRouteIn($route, '/presenter/1234');

testRouteIn($route, '/presenter/');



__halt_compiler() ?>

------EXPECT------
==> /presenter/12/

string(9) "Presenter"

array(2) {
	"id" => string(2) "12"
	"test" => string(9) "testvalue"
}

string(28) "/presenter/12?test=testvalue"

==> /presenter/1234

not matched

==> /presenter/

not matched
