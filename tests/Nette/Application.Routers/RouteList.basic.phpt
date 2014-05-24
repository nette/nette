<?php

/**
 * Test: Nette\Application\Routers\RouteList default usage.
 */

use Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$list = new RouteList();
$list[] = new Route('admin/<presenter>/<action=default>/<id= \d{1,3}>', array('module' => 'Admin'));
$list[] = new Route('<presenter>/<action=default>/<id= \d{1,3}>', array('module' => 'Front'));


Assert::same('http://example.com/homepage/', testRouteOut($list, 'Front:Homepage'));
Assert::same('http://example.com/admin/dashboard/', testRouteOut($list, 'Admin:Dashboard'));
Assert::null(testRouteOut($list, 'Homepage'));

testRouteIn($list, '/presenter/action/12/any');

testRouteIn($list, '/presenter/action/12/', 'Front:Presenter', array(
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
), '/presenter/action/12?test=testvalue');

testRouteIn($list, '/admin/presenter/action/12/any');

testRouteIn($list, '/admin/presenter/action/12/', 'Admin:Presenter', array(
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
), '/admin/presenter/action/12?test=testvalue');

testRouteIn($list, '/');
