<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 * @version    $Id$
 */

/*use Nette\Debug;*/
/*use Nette\Application\CliRouter;*/
/*use Nette\Web\HttpRequest;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @package    Nette\Application
 * @subpackage UnitTests
 */
class NetteApplicationCliRouterTest extends PHPUnit_Framework_TestCase
{


	/**
	 * Basic usage test.
	 * @return void
	 */
	public function testBasicUsage()
	{
		$router = new CliRouter(array(
			'id' => 12,
			'user' => 'anyvalue',
		));

        // php.exe app.phpc homepage:default name --verbose -user "john doe" "-pass=se cret" /wait
		$_SERVER['argv'] = array(
			'app.phpc',
			'homepage:default',
			'name',
			'--verbose',
			'-user',
			'john doe',
			'-pass=se cret',
			'/wait',
		);
		$httpRequest = new HttpRequest;
		$req = $router->match($httpRequest);
		$this->assertEquals("homepage", $req->getPresenterName());
		$this->assertEquals("default", $req->params["action"]);
		$this->assertEquals("12", $req->params["id"]);
		$this->assertEquals("john doe", $req->params["user"]);
		$this->assertEquals("se cret", $req->params["pass"]);
		$this->assertEquals(TRUE, $req->params["wait"]);
		$this->assertEquals(TRUE, $req->isMethod('cli'));

		$url = $router->constructUrl($req, $httpRequest);
		$this->assertEquals(NULL, $url);
	}



	/**
	 * Invalid test.
	 * @return void
	 */
	public function testInvalid()
	{
		$router = new CliRouter;
		$_SERVER['argv'] = 1;
		$httpRequest = new HttpRequest;
		$req = $router->match($httpRequest);
		$this->assertEquals(NULL, $req);
	}

}
