<?php

/**
 * Test: Nette\Application\Routers\Route with slash in path.
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 */

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<param>', array(
	'presenter' => 'Presenter',
));

testRouteIn($route, '/a/b');
Assert::null( testRouteOut($route, 'Presenter', array('param' => 'a/b')) );


$route = new Route('<param .+>', array(
	'presenter' => 'Presenter',
));

testRouteIn($route, '/a/b', 'Presenter', array(
	'param' => 'a/b',
	'test' => 'testvalue',
), '/a/b?test=testvalue');
