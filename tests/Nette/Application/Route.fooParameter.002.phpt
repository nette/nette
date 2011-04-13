<?php

/**
 * Test: Nette\Application\Routers\Route with FooParameter
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('index<?.xml>/', array(
	'presenter' => 'DefaultPresenter',
));


testRouteIn($route, '/index.');

testRouteIn($route, '/index.xml', 'DefaultPresenter', array(
	'test' => 'testvalue',
), '/index.xml/?test=testvalue');

testRouteIn($route, '/index.php');

testRouteIn($route, '/index', 'DefaultPresenter', array(
	'test' => 'testvalue',
), '/index.xml/?test=testvalue');
