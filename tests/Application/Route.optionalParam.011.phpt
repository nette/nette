<?php

/**
 * Test: Nette\Application\Route with optional sequence precedence.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';


$route = new Route('[<one>/][<two>]', array(
));

testRouteIn($route, '/one');


T::note();


$route = new Route('[<one>/]<two>', array(
	'two' => NULL,
));

testRouteIn($route, '/one');



__halt_compiler() ?>

------EXPECT------
==> /one

string(14) "querypresenter"

array(3) {
	"one" => string(3) "one"
	"two" => NULL
	"test" => string(9) "testvalue"
}

string(45) "/one/?test=testvalue&presenter=querypresenter"

===

==> /one

string(14) "querypresenter"

array(3) {
	"one" => string(3) "one"
	"two" => NULL
	"test" => string(9) "testvalue"
}

string(45) "/one/?test=testvalue&presenter=querypresenter"
