<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 * @version    $Id$
 */

/*use Nette\Debug;*/
/*use Nette\Web\HttpRequest;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class NetteWebHttpRequestTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Request test.
	 * @return void
	 */
	public function testRequest()
	{
		$_SERVER = array(
			'HTTPS' => 'On',
			'HTTP_HOST' => 'nettephp.com:8080',
			'QUERY_STRING' => 'x param=val.&pa%%72am=val2)',
			'REMOTE_ADDR' => '192.168.188.66',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/file.php?x param=val.&pa%%72am=val2)',
			'SCRIPT_FILENAME' => '/public_html/www/file.php',
			'SCRIPT_NAME' => '/file.php',
		);

		$request = new HttpRequest;
		$request->addUriFilter('%20', '', PHP_URL_PATH);
		$request->addUriFilter('[.,)]$');

		$this->assertEquals('GET', $request->getMethod());
		$this->assertEquals(TRUE, $request->isSecured());
		$this->assertEquals('192.168.188.66', $request->getRemoteAddress());

		$this->assertEquals('/file.php', $request->getUri()->scriptPath);
		$this->assertEquals('https', $request->getUri()->scheme);
		$this->assertEquals('', $request->getUri()->user);
		$this->assertEquals('', $request->getUri()->pass);
		$this->assertEquals('nettephp.com', $request->getUri()->host);
		$this->assertEquals(8080, $request->getUri()->port);
		$this->assertEquals('/file.php', $request->getUri()->path);
		$this->assertEquals("pa\x072am=val2&x param=val.", $request->getUri()->query);
		$this->assertEquals('', $request->getUri()->fragment);
		$this->assertEquals('nettephp.com:8080', $request->getUri()->authority);
		$this->assertEquals('https://nettephp.com:8080', $request->getUri()->hostUri);
		$this->assertEquals('https://nettephp.com:8080/', $request->getUri()->baseUri);
		$this->assertEquals('/', $request->getUri()->basePath);
		$this->assertEquals('file.php', $request->getUri()->relativeUri);
		$this->assertEquals("https://nettephp.com:8080/file.php?pa\x072am=val2&x param=val.", $request->getUri()->absoluteUri);
		$this->assertEquals('', $request->getUri()->pathInfo);

		$this->assertEquals('https', $request->getOriginalUri()->scheme);
		$this->assertEquals('', $request->getOriginalUri()->user);
		$this->assertEquals('', $request->getOriginalUri()->pass);
		$this->assertEquals('nettephp.com', $request->getOriginalUri()->host);
		$this->assertEquals(8080, $request->getOriginalUri()->port);
		$this->assertEquals('/file.php', $request->getOriginalUri()->path);
		$this->assertEquals('x param=val.&pa%%72am=val2)', $request->getOriginalUri()->query);
		$this->assertEquals('', $request->getOriginalUri()->fragment);
		$this->assertEquals('val.', $request->getQuery('x_param'));
		$this->assertEquals('val2', $request->getQuery('pa%ram'));
		$this->assertEquals('', $request->getPostRaw());
		$this->assertEquals('nettephp.com:8080', $request->headers['host']);
	}



	/**
	 * Invalid encoding test.
	 * @return void
	 */
	public function testInvalidEncoding()
	{
		define("INVALID", "\x76\xC4\xC5\xBE");

		$_GET = array(
			'test' => INVALID,
			INVALID => INVALID,
			'array' => array(INVALID => 1),
		);

		$_POST = array(
			'test' => INVALID,
			INVALID => INVALID,
			'array' => array(INVALID => 1),
		);

		$_FILES = array(
			INVALID => array(
				'name' => INVALID,
				'type' => 'text/plain',
				'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
				'error' => 0,
				'size' => 209,
			),
			'file1' => array(
				'name' => 'readme.txt',
				'type' => 'text/plain',
				'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
				'error' => 0,
				'size' => 209,
			),
		);

		$_COOKIE = array(
			'test' => INVALID,
			INVALID => INVALID,
			'array' => array(INVALID => 1),
		);

		$request = new HttpRequest;

		$this->assertEquals(INVALID, $request->getQuery('test'));
		$this->assertEquals(INVALID, $request->getQuery(INVALID));
		$this->assertEquals("1", $request->query['array'][INVALID]);
		$this->assertEquals(INVALID, $request->getPost('test'));
		$this->assertEquals(INVALID, $request->getPost(INVALID));
		$this->assertEquals("1", $request->post['array'][INVALID]);
		$this->assertEquals(INVALID, $request->getCookie('test'));
		$this->assertEquals(INVALID, $request->getCookie(INVALID));
		$this->assertEquals("1", $request->cookies['array'][INVALID]);

		$request->setEncoding('UTF-8');

		$this->assertEquals("v\xc5\xbe", $request->getQuery('test'));
		$this->assertNull($request->getQuery(INVALID));
		$this->assertFalse(isset($request->query['array'][INVALID]));
		$this->assertEquals("v\xc5\xbe", $request->getPost('test'));
		$this->assertNull($request->getPost(INVALID));
		$this->assertFalse(isset($request->post['array'][INVALID]));
		$this->assertEquals("v\xc5\xbe", $request->getCookie('test'));
		$this->assertNull($request->getCookie(INVALID));
		$this->assertFalse(isset($request->cookies['array'][INVALID]));
	}



	/**
	 * $_FILES test.
	 * @return void
	 */
	public function testFiles()
	{
		$_FILES = array(
			'file1' => array(
				'name' => 'readme.txt',
				'type' => 'text/plain',
				'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
				'error' => 0,
				'size' => 209,
			),

			'file2' => array(
				'name' => array(
					2 => 'license.txt',
				),

				'type' => array(
					2 => 'text/plain',
				),

				'tmp_name' => array(
					2 => 'C:\\PHP\\temp\\php1D5C.tmp',
				),

				'error' => array(
					2 => 0,
				),

				'size' => array(
					2 => 3013,
				),
			),

			'file3' => array(
				'name' => array(
					'y' => array(
						'z' => 'default.htm',
					),
					1 => 'logo.gif',
				),

				'type' => array(
					'y' => array(
						'z' => 'text/html',
					),
					1 => 'image/gif',
				),

				'tmp_name' => array(
					'y' => array(
						'z' => 'C:\\PHP\\temp\\php1D5D.tmp',
					),
					1 => 'C:\\PHP\\temp\\php1D5E.tmp',
				),

				'error' => array(
					'y' => array(
						'z' => 0,
					),
					1 => 0,
				),

				'size' => array(
					'y' => array(
						'z' => 26320,
					),
					1 => 3519,
				),
			),
		);

		$request = new HttpRequest;

		$this->assertType("HttpUploadedFile", $request->files["file1"]);
		$this->assertType("HttpUploadedFile", $request->files["file2"][2]);
		$this->assertType("HttpUploadedFile", $request->files["file3"]["y"]["z"]);
		$this->assertType("HttpUploadedFile", $request->files["file3"][1]);

		$this->assertFalse(isset($request->files['file0']));
		$this->assertTrue(isset($request->files['file1']));

		$this->assertNull($request->getFile('file1', 'a'));
	}

}
