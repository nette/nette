<?php

/**
 * Test: Nette\Application\Routers\Route default usage.
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';



$route = new Route('<presenter>/<action=default>/<id= \d{1,3}>');

Assert::same( 'http://example.com/homepage/', testRouteOut($route, 'Homepage') );

Assert::same( 'http://example.com/homepage/', testRouteOut($route, 'Homepage', array('action' => 'default')) );

Assert::null( testRouteOut($route, 'Homepage', array('id' => 'word')) );

Assert::same( 'http://example.com/front.homepage/', testRouteOut($route, 'Front:Homepage') );

testRouteIn($route, '/presenter/action/12/any');

testRouteIn($route, '/presenter/action/12/', 'Presenter', array(
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
), '/presenter/action/12?test=testvalue');

testRouteIn($route, '/presenter/action/12', 'Presenter', array(
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
), '/presenter/action/12?test=testvalue');

testRouteIn($route, '/presenter/action/1234');

testRouteIn($route, '/presenter/action/', 'Presenter', array(
	'action' => 'action',
	'id' => '',
	'test' => 'testvalue',
), '/presenter/action/?test=testvalue');

testRouteIn($route, '/presenter/action', 'Presenter', array(
	'action' => 'action',
	'id' => '',
	'test' => 'testvalue',
), '/presenter/action/?test=testvalue');

testRouteIn($route, '/presenter/', 'Presenter', array(
	'id' => '',
	'action' => 'default',
	'test' => 'testvalue',
), '/presenter/?test=testvalue');

testRouteIn($route, '/presenter', 'Presenter', array(
	'id' => '',
	'action' => 'default',
	'test' => 'testvalue',
), '/presenter/?test=testvalue');

testRouteIn($route, '/');
