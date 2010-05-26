<?php

/**
 * Test: Nette\Application\Route with WithAbsolutePath
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../NetteTest/initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('/<abspath>/', array(
	'presenter' => 'Default',
	'action' => 'default',
));

testRouteIn($route, '/abc');



__halt_compiler() ?>

------EXPECT------
==> /abc

string(7) "Default"

array(3) {
	"abspath" => string(3) "abc"
	"action" => string(7) "default"
	"test" => string(9) "testvalue"
}

string(20) "/abc/?test=testvalue"
