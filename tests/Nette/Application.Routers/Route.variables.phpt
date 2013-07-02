<?php

/**
 * Test: Nette\Application\Routers\Route with %variables%
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 */

use Nette\Application\Routers\Route;


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
