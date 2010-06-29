<?php

/**
 * Test: Nette\Application\Route with optional sequence and two parameters.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';


$route = new Route('[<one [a-z]+><two [0-9]+>]', array(
	'one' => 'a',
	'two' => '1',
));

testRouteIn($route, '/a1');

testRouteIn($route, '/x1');

testRouteIn($route, '/a2');

testRouteIn($route, '/x2');



__halt_compiler() ?>

------EXPECT------
==> /a1

string(14) "querypresenter"

array(3) {
	"one" => string(1) "a"
	"two" => string(1) "1"
	"test" => string(9) "testvalue"
}

string(41) "/?test=testvalue&presenter=querypresenter"

==> /x1

string(14) "querypresenter"

array(3) {
	"one" => string(1) "x"
	"two" => string(1) "1"
	"test" => string(9) "testvalue"
}

string(43) "/x1?test=testvalue&presenter=querypresenter"

==> /a2

string(14) "querypresenter"

array(3) {
	"one" => string(1) "a"
	"two" => string(1) "2"
	"test" => string(9) "testvalue"
}

string(43) "/a2?test=testvalue&presenter=querypresenter"

==> /x2

string(14) "querypresenter"

array(3) {
	"one" => string(1) "x"
	"two" => string(1) "2"
	"test" => string(9) "testvalue"
}

string(43) "/x2?test=testvalue&presenter=querypresenter"
