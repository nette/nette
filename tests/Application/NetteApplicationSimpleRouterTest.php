<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Debug;*/
/*use Nette\Application\SimpleRouter;*/
/*use Nette\Web\HttpRequest;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



class MockHttpRequest extends /*Nette\Web\*/HttpRequest
{

	public function setQuery(array $query)
	{
		$this->query = $query;
	}

}



/**
 * @package    Nette\Application
 * @subpackage UnitTests
 */
class NetteApplicationSimpleRouterTest extends PHPUnit_Framework_TestCase
{


	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$_SERVER = array(
			'HTTP_HOST' => 'nettephp.com',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/file.php',
			'SCRIPT_FILENAME' => '/public_html/www/file.php',
			'SCRIPT_NAME' => '/file.php',
		);
	}



	/**
	 * Basic usage test.
	 * @return void
	 */
	public function testBasicUsage()
	{
		$router = new SimpleRouter(array(
			'id' => 12,
			'any' => 'anyvalue',
		));

		$httpRequest = new MockHttpRequest;
		$httpRequest->setQuery(array(
			'presenter' => 'myPresenter',
			'action' => 'action',
			'id' => '12',
			'test' => 'testvalue',
		));

		$req = $router->match($httpRequest);
		$this->assertEquals("myPresenter", $req->getPresenterName());
		$this->assertEquals("action", $req->params["action"]);
		$this->assertEquals("12", $req->params["id"]);
		$this->assertEquals("testvalue", $req->params["test"]);
		$this->assertEquals("anyvalue", $req->params["any"]);

		$url = $router->constructUrl($req, $httpRequest);
		$this->assertEquals("http://nettephp.com/file.php?action=action&test=testvalue&presenter=myPresenter", $url);
	}



	/**
	 * With module test.
	 * @return void
	 */
	public function testWithModule()
	{
		$router = new SimpleRouter(array(
			'module' => 'main:sub',
		));

		$httpRequest = new MockHttpRequest;
		$httpRequest->setQuery(array(
			'presenter' => 'myPresenter',
		));

		$req = $router->match($httpRequest);
		$this->assertEquals("main:sub:myPresenter", $req->getPresenterName());

		$url = $router->constructUrl($req, $httpRequest);
		$this->assertEquals("http://nettephp.com/file.php?presenter=myPresenter", $url);

		$req = new /*Nette\Application\*/PresenterRequest(
			'othermodule:presenter',
			/*Nette\Web\*/HttpRequest::GET,
			array()
		);
		$url = $router->constructUrl($req, $httpRequest);
		$this->assertEquals(NULL, $url);
	}



	/**
	 * Secured test.
	 * @return void
	 */
	public function testSecured()
	{
		$router = new SimpleRouter(array(
			'id' => 12,
			'any' => 'anyvalue',
		), SimpleRouter::SECURED);

		$httpRequest = new MockHttpRequest;
		$httpRequest->setQuery(array(
			'presenter' => 'myPresenter',
		));

		$req = new /*Nette\Application\*/PresenterRequest(
			'othermodule:presenter',
			/*Nette\Web\*/HttpRequest::GET,
			array()
		);

		$url = $router->constructUrl($req, $httpRequest);
		$this->assertEquals("https://nettephp.com/file.php?presenter=othermodule%3Apresenter", $url);
	}

}
