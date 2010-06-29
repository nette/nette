<?php

/**
 * Test: Nette\Application\Route with ExtraDefaultParam
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>/<action>/<id \d{1,3}>/', array(
	'extra' => NULL,
));


testRouteIn($route, '/presenter/action/12/any');

testRouteIn($route, '/presenter/action/12');

testRouteIn($route, '/presenter/action/1234');

testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/presenter');

testRouteIn($route, '/');



__halt_compiler() ?>

------EXPECT------
==> /presenter/action/12/any

not matched

==> /presenter/action/12

string(9) "Presenter"

array(4) {
	"action" => string(6) "action"
	"id" => string(2) "12"
	"extra" => NULL
	"test" => string(9) "testvalue"
}

string(36) "/presenter/action/12/?test=testvalue"

==> /presenter/action/1234

not matched

==> /presenter/action/

not matched

==> /presenter

not matched

==> /

not matched
