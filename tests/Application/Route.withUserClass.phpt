<?php

/**
 * Test: Nette\Application\Route with WithUserClass
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



Route::addStyle('#numeric');
Route::setStyleProperty('#numeric', Route::PATTERN, '\d{1,3}');

$route = new Route('<presenter>/<id #numeric>', array());

testRouteIn($route, '/presenter/12/');

testRouteIn($route, '/presenter/1234');

testRouteIn($route, '/presenter/');



__halt_compiler() ?>

------EXPECT------
==> /presenter/12/

"Presenter"

array(
	"id" => "12"
	"test" => "testvalue"
)

"/presenter/12?test=testvalue"

==> /presenter/1234

not matched

==> /presenter/

not matched
