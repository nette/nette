<?php

/**
 * Test: Nette\Application\Routers\Route with NoDefaultParams
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>/<action>/<extra>', array(
));

testRouteIn($route, '/presenter/action/12', 'Presenter', array(
	'action' => 'action',
	'extra' => '12',
	'test' => 'testvalue',
), NULL);
