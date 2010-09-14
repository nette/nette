<?php

/**
 * Test: Nette\Application\Route with DashInParameter
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<para-meter>', array(
	'presenter' => 'Presenter',
));

testRouteIn($route, '/any', 'Presenter', array(
	'para-meter' => 'any',
	'test' => 'testvalue',
), '/any?test=testvalue');
