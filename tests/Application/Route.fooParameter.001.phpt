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



$route = new Route('index<?.xml \.html?|\.php|>/', array(
	'presenter' => 'DefaultPresenter',
));

testRoute($route, '/index.');

testRoute($route, '/index.xml');

testRoute($route, '/index.php');

testRoute($route, '/index.htm');

testRoute($route, '/index');



__halt_compiler();

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

string(16) "DefaultPresenter"

array(1) {
	"test" => string(9) "testvalue"
}

string(26) "/index.xml/?test=testvalue"

==> /index.htm

string(16) "DefaultPresenter"

array(1) {
	"test" => string(9) "testvalue"
}

string(26) "/index.xml/?test=testvalue"

==> /index

string(16) "DefaultPresenter"

array(1) {
	"test" => string(9) "testvalue"
}

string(26) "/index.xml/?test=testvalue"
