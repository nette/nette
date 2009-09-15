<?php

/**
 * Test: Route with UrlEncoding
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Route.inc';

/*use Nette\Application\Route;*/


$route = new Route('<param>', array(
	'presenter' => 'Presenter',
));


testRoute($route, '/a%3Ab');


__halt_compiler();

------EXPECT------
==> /a%3Ab

string(9) "Presenter"

array(2) {
	"param" => string(3) "a:b"
	"test" => string(9) "testvalue"
}

string(21) "/a%3Ab?test=testvalue"
