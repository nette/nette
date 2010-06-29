<?php

/**
 * Test: Nette\Application\Route with DashInParameter
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\Route;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Route.inc';



$route = new Route('<para-meter>', array(
	'presenter' => 'Presenter',
));


testRouteIn($route, '/any');



__halt_compiler() ?>

------EXPECT------
==> /any

string(9) "Presenter"

array(2) {
	"para-meter" => string(3) "any"
	"test" => string(9) "testvalue"
}

string(19) "/any?test=testvalue"
