<?php

/**
 * Test: Nette\Application\Route with "required" optional sequence.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';


$route = new Route('index[!.html]', array(
));

testRouteIn($route, '/index.html');

testRouteIn($route, '/index');



__halt_compiler();

------EXPECT------
==> /index.html

string(14) "querypresenter"

array(1) {
	"test" => string(9) "testvalue"
}

string(51) "/index.html?test=testvalue&presenter=querypresenter"

==> /index

string(14) "querypresenter"

array(1) {
	"test" => string(9) "testvalue"
}

string(51) "/index.html?test=testvalue&presenter=querypresenter"
