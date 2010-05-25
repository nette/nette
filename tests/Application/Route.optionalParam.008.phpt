<?php

/**
 * Test: Nette\Application\Route with "required" optional sequences III.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';


$route = new Route('[!<lang [a-z]{2}>[-<sub>]/]<name>[/page-<page>]', array(
	'sub' => 'cz',
	'lang' => 'cs',
));

testRouteIn($route, '/cs-cz/name');

testRouteIn($route, '/cs-xx/name');

testRouteIn($route, '/name');



__halt_compiler() ?>

------EXPECT------
==> /cs-cz/name

string(14) "querypresenter"

array(5) {
	"lang" => string(2) "cs"
	"sub" => string(2) "cz"
	"name" => string(4) "name"
	"page" => NULL
	"test" => string(9) "testvalue"
}

string(48) "/cs/name?test=testvalue&presenter=querypresenter"

==> /cs-xx/name

string(14) "querypresenter"

array(5) {
	"lang" => string(2) "cs"
	"sub" => string(2) "xx"
	"name" => string(4) "name"
	"page" => NULL
	"test" => string(9) "testvalue"
}

string(51) "/cs-xx/name?test=testvalue&presenter=querypresenter"

==> /name

string(14) "querypresenter"

array(5) {
	"name" => string(4) "name"
	"sub" => string(2) "cz"
	"lang" => string(2) "cs"
	"page" => NULL
	"test" => string(9) "testvalue"
}

string(48) "/cs/name?test=testvalue&presenter=querypresenter"
