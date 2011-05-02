<?php

/**
 * Test: Nette\Application\Routers\Route with 'required' optional sequences III.
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('[!<lang [a-z]{2}>[-<sub>]/]<name>[/page-<page>]', array(
	'sub' => 'cz',
	'lang' => 'cs',
));

testRouteIn($route, '/cs-cz/name', 'querypresenter', array(
	'lang' => 'cs',
	'sub' => 'cz',
	'name' => 'name',
	'page' => NULL,
	'test' => 'testvalue',
), '/cs/name?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/cs-xx/name', 'querypresenter', array(
	'lang' => 'cs',
	'sub' => 'xx',
	'name' => 'name',
	'page' => NULL,
	'test' => 'testvalue',
), '/cs-xx/name?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/name', 'querypresenter', array(
	'name' => 'name',
	'sub' => 'cz',
	'lang' => 'cs',
	'page' => NULL,
	'test' => 'testvalue',
), '/cs/name?test=testvalue&presenter=querypresenter');
