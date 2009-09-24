<?php

/**
 * Test: Nette\Application\Route with optional parameters 2.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';


$route = new Route('{<lang [a-z]{2}>}/<name>', array(
	'lang' => 'cz',
));

testRoute($route, '/cs/name');

testRoute($route, '//name');

testRoute($route, '/');




__halt_compiler();

------EXPECT------
==> /cs/name

string(14) "querypresenter"

array(3) {
	"lang" => string(2) "cs"
	"name" => string(4) "name"
	"test" => string(9) "testvalue"
}

string(48) "/cs/name?test=testvalue&presenter=querypresenter"

==> //name

string(14) "querypresenter"

array(3) {
	"name" => string(4) "name"
	"lang" => string(2) "cz"
	"test" => string(9) "testvalue"
}

NULL

==> /

not matched
