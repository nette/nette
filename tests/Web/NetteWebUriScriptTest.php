<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Debug;*/
/*use Nette\Web\UriScript;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class NetteWebUriScriptTest extends PHPUnit_Framework_TestCase
{

	/** @var UriScript */
	protected $uri;



	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->uri = new UriScript('http://nettephp.com:8080/file.php?q=search');
	}



	/**
	 * Parse test.
	 * @return void
	 */
	public function testParse()
	{
		$this->assertEquals(NULL, $this->uri->scriptPath);
		$this->assertEquals('http://nettephp.com:8080', $this->uri->baseUri);
		$this->assertEquals(false, $this->uri->basePath);
		$this->assertEquals('file.php', $this->uri->relativeUri);
		$this->assertEquals('/file.php', $this->uri->pathInfo);
	}



	/**
	 * Modify test.
	 * @return void
	 */
	public function testModify()
	{
		$this->uri->path = '/test/';
		$this->uri->scriptPath = '/test/index.php';

		$this->assertEquals('/test/index.php', $this->uri->scriptPath);
		$this->assertEquals('http://nettephp.com:8080/test/', $this->uri->baseUri);
		$this->assertEquals('/test/', $this->uri->basePath);
		$this->assertEquals('', $this->uri->relativeUri);
		$this->assertEquals('', $this->uri->pathInfo);
		$this->assertEquals('http://nettephp.com:8080/test/?q=search', $this->uri->absoluteUri);
	}

}
