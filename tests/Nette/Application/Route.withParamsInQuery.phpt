<?php

/**
 * Test: Nette\Application\Routers\Route with WithParamsInQuery
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('<action> ? <presenter>', array(
	'presenter' => 'Default',
	'action' => 'default',
));

testRouteIn($route, '/action/', 'querypresenter', array(
	'action' => 'action',
	'test' => 'testvalue',
), '/action?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/', 'querypresenter', array(
	'action' => 'default',
	'test' => 'testvalue',
), '/?test=testvalue&presenter=querypresenter');
