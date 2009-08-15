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
/*use Nette\Web\Uri;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class NetteWebUriTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Http scheme test.
	 * @return void
	 */
	public function testHttpScheme()
	{
		$uri = new Uri('http://username:password@hostname:60/path?arg=value#anchor');

		$this->assertEquals('http', $uri->scheme);
		$this->assertEquals('username', $uri->user);
		$this->assertEquals('password', $uri->pass);
		$this->assertEquals('hostname', $uri->host);
		$this->assertEquals(60, $uri->port);
		$this->assertEquals('/path', $uri->path);
		$this->assertEquals('arg=value', $uri->query);
		$this->assertEquals('anchor', $uri->fragment);
		$this->assertEquals('hostname:60', $uri->authority);
		$this->assertEquals('http://hostname:60', $uri->hostUri);
		$this->assertEquals('http://hostname:60/path?arg=value#anchor', $uri->absoluteUri);
		$this->assertEquals('http://hostname:60/path?arg=value#anchor', (string) $uri);
	}



	/**
	 * Canonicalize test.
	 * @return void
	 */
	public function testCanonicalize()
	{
		$uri = new Uri('http://hostname/path?arg=value&arg2=v%20a%26l%3Du%2Be');
		$this->assertEquals('arg=value&arg2=v%20a%26l%3Du%2Be', $uri->query);

		$uri->canonicalize();
		$this->assertEquals('arg2=v a%26l%3Du%2Be&arg=value', $uri->query);
	}



	/**
	 * Ftp scheme test.
	 * @return void
	 */
	public function testFtpScheme()
	{
		$uri = new Uri('ftp://ftp.is.co.za/rfc/rfc3986.txt');

		$this->assertEquals('ftp', $uri->scheme);
		$this->assertEquals('', $uri->user);
		$this->assertEquals('', $uri->pass);
		$this->assertEquals('ftp.is.co.za', $uri->host);
		$this->assertEquals(21, $uri->port);
		$this->assertEquals('/rfc/rfc3986.txt', $uri->path);
		$this->assertEquals('', $uri->query);
		$this->assertEquals('', $uri->fragment);
		$this->assertEquals('ftp.is.co.za', $uri->authority);
		$this->assertEquals('ftp://ftp.is.co.za', $uri->hostUri);
		$this->assertEquals('ftp://ftp.is.co.za/rfc/rfc3986.txt', $uri->absoluteUri);
	}



	/**
	 * File scheme test.
	 * @return void
	 */
	public function testFileScheme()
	{
		$uri = new Uri('file://localhost/D:/dokumentace/rfc3986.txt');

		$this->assertEquals('file', $uri->scheme);
		$this->assertEquals('', $uri->user);
		$this->assertEquals('', $uri->pass);
		$this->assertEquals('localhost', $uri->host);
		$this->assertEquals(NULL, $uri->port);
		$this->assertEquals('/D:/dokumentace/rfc3986.txt', $uri->path);
		$this->assertEquals('', $uri->query);
		$this->assertEquals('', $uri->fragment);
		$this->assertEquals('file://localhost/D:/dokumentace/rfc3986.txt', (string) $uri);
	}



	/**
	 * File scheme 2 test.
	 * @return void
	 */
	public function testFileScheme2()
	{
		$uri = new Uri('file:///D:/dokumentace/rfc3986.txt');

		$this->assertEquals('file', $uri->scheme);
		$this->assertEquals('', $uri->user);
		$this->assertEquals('', $uri->pass);
		$this->assertEquals('', $uri->host);
		$this->assertEquals(NULL, $uri->port);
		$this->assertEquals('D:/dokumentace/rfc3986.txt', $uri->path);
		$this->assertEquals('', $uri->query);
		$this->assertEquals('', $uri->fragment);
		$this->assertEquals('file://D:/dokumentace/rfc3986.txt', (string) $uri);
	}



	/**
	 * Malformed URI test.
	 * @return void
	 */
	public function testMalformedUri()
	{
		$this->setExpectedException('InvalidArgumentException', 'Malformed or unsupported URI');
		$uri = new Uri(':');
	}



	/**
	 * Is Equal? test.
	 * @return void
	 */
	public function testIsEqual()
	{
		$uri = new Uri('http://exampl%65.COM?text=foo%20bar+foo');
		$uri->canonicalize();
		$this->assertTrue($uri->isEqual('http://example.com/?text=foo+bar%20foo'));
	}



	/**
	 * Query test.
	 * @return void
	 */
	public function testQuery()
	{
		$uri = new Uri('http://hostname/path?arg=value');
		$this->assertEquals('arg=value', $uri->query);

		$uri->appendQuery(NULL);
		$this->assertEquals('arg=value', $uri->query);

		$uri->appendQuery(array(NULL));
		$this->assertEquals('arg=value', $uri->query);

		$uri->appendQuery('arg2=value2');
		$this->assertEquals('arg=value&arg2=value2', $uri->query);

		$uri->appendQuery(array('arg3' => 'value3'));
		$this->assertEquals('arg=value&arg2=value2&arg3=value3', $uri->query);

		$uri->setQuery(array('arg3' => 'value3'));
		$this->assertEquals('arg3=value3', $uri->query);
	}

}
