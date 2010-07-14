<?php

/**
 * Test: Nette\Application\Route with WithDefaultPresenterAndAction
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>/<action>', array(
	'presenter' => 'Default',
	'action' => 'default',
));


testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/default/default/');

testRouteIn($route, '/presenter');

testRouteIn($route, '/');



__halt_compiler() ?>

------EXPECT------
==> /presenter/action/

"Presenter"

array(
	"action" => "action"
	"test" => "testvalue"
)

"/presenter/action?test=testvalue"

==> /default/default/

"Default"

array(
	"action" => "default"
	"test" => "testvalue"
)

"/?test=testvalue"

==> /presenter

"Presenter"

array(
	"action" => "default"
	"test" => "testvalue"
)

"/presenter/?test=testvalue"

==> /

"Default"

array(
	"action" => "default"
	"test" => "testvalue"
)

"/?test=testvalue"
