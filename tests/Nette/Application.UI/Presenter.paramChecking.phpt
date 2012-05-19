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
	function actionDefault($a, $b = NULL, array $c, array $d = NULL, $e = 1, $f = 1.0, $g = FALSE)
	{
	}

}


$container = id(new Nette\Config\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$presenter = new TestPresenter;
$container->callMethod(array($presenter, 'injectPrimary'), array(7 => $container->parameters['debugMode']));


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
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'a', expected scalar.");


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('b' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'b', expected scalar.");


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('c' => 1));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'c', expected array.");


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('d' => 1));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'd', expected array.");


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('e' => 1.1));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'e', expected integer.");


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('e' => '1 '));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'e', expected integer.");


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('f' => '1 '));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'f', expected double.");


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('g' => ''));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'g', expected boolean.");
