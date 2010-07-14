<?php

/**
 * Test: Nette\Application\Route with NoDefaultParams
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>/<action>/<extra>', array(
));


testRouteIn($route, '/presenter/action/12');



__halt_compiler() ?>

------EXPECT------
==> /presenter/action/12

"Presenter"

array(
	"action" => "action"
	"extra" => "12"
	"test" => "testvalue"
)

NULL
