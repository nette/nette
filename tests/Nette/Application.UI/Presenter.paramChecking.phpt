<?php

/**
 * Test: Nette\Application\UI\Presenter and checking params.
 *
 * @author     David Grudl
 * @package    Nette\Application\UI
 * @subpackage UnitTests
 */

use Nette\Http,
	Nette\Application;



require __DIR__ . '/../bootstrap.php';



class TestPresenter extends Application\UI\Presenter
{
	function actionDefault($a, $b = NULL, array $c, array $d = NULL)
	{
	}

}


$container = id(new Nette\Config\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$presenter = new TestPresenter($container);


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('action' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Action name is not alphanumeric string.');


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('do' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Signal name is not string.');


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('a' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'a'.");


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('b' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'b'.");


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('c' => 1));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'c'.");


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('d' => 1));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'd'.");
