<?php

/**
 * Test: Nette\Application\Route with FooParameter
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';



$route = new Route('index<?.xml>/', array(
	'presenter' => 'DefaultPresenter',
));


testRouteIn($route, '/index.');

testRouteIn($route, '/index.xml');

testRouteIn($route, '/index.php');

testRouteIn($route, '/index');



__halt_compiler() ?>

------EXPECT------
==> /index.

not matched

==> /index.xml

string(16) "DefaultPresenter"

array(1) {
	"test" => string(9) "testvalue"
}

string(26) "/index.xml/?test=testvalue"

==> /index.php

not matched

==> /index

string(16) "DefaultPresenter"

array(1) {
	"test" => string(9) "testvalue"
}

string(26) "/index.xml/?test=testvalue"
