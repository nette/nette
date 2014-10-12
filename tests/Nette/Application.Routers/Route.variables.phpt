<?php

/**
 * Test: Nette\Application\Routers\Route with %variables%
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


testRouteIn(new Route('//<?%domain%>/<path>', 'Default:default'), '/abc', 'Default', array(
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
), '/abc?test=testvalue');


testRouteIn(new Route('//example.<?%tld%>/<path>', 'Default:default'), '/abc', 'Default', array(
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
), '/abc?test=testvalue');


testRouteIn(new Route('//example.com/<?%basePath%>/<path>', 'Default:default'), '/abc', 'Default', array(
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
), '/abc?test=testvalue');


testRouteIn(new Route('//%domain%/<path>', 'Default:default'), '/abc', 'Default', array(
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
), '/abc?test=testvalue');


// alternative
testRouteIn(new Route('//example.%tld%/<path>', 'Default:default'), '/abc', 'Default', array(
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
), '/abc?test=testvalue');


testRouteIn(new Route('//example.com/%basePath%/<path>', 'Default:default'), '/abc', 'Default', array(
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
), '/abc?test=testvalue');


// IP
$url = new Nette\Http\UrlScript('http://192.168.100.100/');
$httpRequest = new Nette\Http\Request($url);
$route = new Route('//%domain%/', 'Default:default');
Assert::same('http://192.168.100.100/', $route->constructUrl($route->match($httpRequest), $url));

$route = new Route('//%tld%/', 'Default:default');
Assert::same('http://192.168.100.100/', $route->constructUrl($route->match($httpRequest), $url));


$url = new Nette\Http\UrlScript('http://[2001:db8::1428:57ab]/');
$httpRequest = new Nette\Http\Request($url);
$route = new Route('//%domain%/', 'Default:default');
Assert::same('http://[2001:db8::1428:57ab]/', $route->constructUrl($route->match($httpRequest), $url));

$route = new Route('//%tld%/', 'Default:default');
Assert::same('http://[2001:db8::1428:57ab]/', $route->constructUrl($route->match($httpRequest), $url));


// special
$url = new Nette\Http\UrlScript('http://localhost/');
$httpRequest = new Nette\Http\Request($url);
$route = new Route('//%domain%/', 'Default:default');
Assert::same('http://localhost/', $route->constructUrl($route->match($httpRequest), $url));

$route = new Route('//%tld%/', 'Default:default');
Assert::same('http://localhost/', $route->constructUrl($route->match($httpRequest), $url));
