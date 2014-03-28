<?php

/**
 * Test: Nette\Application\UI\Presenter and checking params.
 *
 * @author     David Grudl
 */

use Nette\Http,
	Nette\Application,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/mocks.php';


class TestPresenter extends Application\UI\Presenter
{
	/** @persistent */
	public $bool = TRUE;

	function actionDefault($a, $b = NULL, array $c, array $d = NULL, $e = 1, $f = 1.0, $g = FALSE)
	{
	}

}


$presenter = new TestPresenter;
$presenter->injectPrimary(
	new Nette\DI\Container,
	new MockPresenterFactory,
	new MockRouter,
	new MockHttpRequest,
	new Http\Response,
	new MockSession,
	new MockUser,
	new MockTemplateFactory
);


Assert::exception(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('action' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Action name is not alphanumeric string.');


Assert::exception(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('do' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', 'Signal name is not string.');


Assert::exception(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('a' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'a' in method TestPresenter::actionDefault(), expected scalar.");


Assert::exception(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('b' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'b' in method TestPresenter::actionDefault(), expected scalar.");


Assert::exception(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('c' => 1));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'c' in method TestPresenter::actionDefault(), expected array.");


Assert::exception(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('d' => 1));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'd' in method TestPresenter::actionDefault(), expected array.");


Assert::exception(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('e' => 1.1));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'e' in method TestPresenter::actionDefault(), expected integer.");


Assert::exception(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('e' => '1 '));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'e' in method TestPresenter::actionDefault(), expected integer.");


Assert::exception(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('f' => '1 '));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'f' in method TestPresenter::actionDefault(), expected double.");


Assert::exception(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('g' => ''));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for parameter 'g' in method TestPresenter::actionDefault(), expected boolean.");

Assert::exception(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('bool' => array()));
	$presenter->run($request);
}, 'Nette\Application\BadRequestException', "Invalid value for persistent parameter 'bool' in 'Test', expected boolean.");
