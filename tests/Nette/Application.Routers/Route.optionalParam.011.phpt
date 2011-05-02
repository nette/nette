<?php

/**
 * Test: Nette\Application\Routers\Route with optional sequence precedence.
 *
 * @author     David Grudl
 * @package    Nette\Application\Routers
 * @subpackage UnitTests
 */

use Nette\Application\Routers\Route;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('[<one>/][<two>]', array(
));

testRouteIn($route, '/one', 'querypresenter', array(
	'one' => 'one',
	'two' => NULL,
	'test' => 'testvalue',
), '/one/?test=testvalue&presenter=querypresenter');

$route = new Route('[<one>/]<two>', array(
	'two' => NULL,
));

testRouteIn($route, '/one', 'querypresenter', array(
	'one' => 'one',
	'two' => NULL,
	'test' => 'testvalue',
), '/one/?test=testvalue&presenter=querypresenter');
