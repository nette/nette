<?php

/**
 * Test: Nette\Application\Route with optional sequence.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';


$route = new Route('index[.html]', array(
));

testRouteIn($route, '/index.html');

testRouteIn($route, '/index');



__halt_compiler() ?>

------EXPECT------
==> /index.html

string(14) "querypresenter"

array(1) {
	"test" => string(9) "testvalue"
}

string(46) "/index?test=testvalue&presenter=querypresenter"

==> /index

string(14) "querypresenter"

array(1) {
	"test" => string(9) "testvalue"
}

string(46) "/index?test=testvalue&presenter=querypresenter"
