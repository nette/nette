<?php

/**
 * Test: Nette\Application\Routers\Route with CombinedUrlParam
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('extra<presenter>/<action>', array(
	'presenter' => 'Default',
	'action' => 'default',
));


testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/extrapresenter/action/', 'Presenter', array(
	'action' => 'action',
	'test' => 'testvalue',
), '/extrapresenter/action?test=testvalue');

testRouteIn($route, '/extradefault/default/', 'Default', array(
	'action' => 'default',
	'test' => 'testvalue',
), '/extra?test=testvalue');

testRouteIn($route, '/extra', 'Default', array(
	'action' => 'default',
	'test' => 'testvalue',
), '/extra?test=testvalue');

testRouteIn($route, '/');
