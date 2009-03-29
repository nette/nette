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
/*use Nette\Web\Ftp;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class NetteWebFtpTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Download test.
	 * @return void
	 */
	public function testDownload()
	{

		$ftp = new Ftp;
		// Opens an FTP connection to the specified host
		$ftp->connect('ftp.nettephp.com');
		$ftp->pasv(TRUE);
		// Login with username and password
		$ftp->login('nette@php7.org', 'anonymous');

		// Download file 'README' to local temporary file
		$temp = tmpfile();
		$ftp->fget($temp, 'README', Ftp::ASCII);

		// echo file
		fseek($temp, 0);
		$this->assertEquals("Nette Framework rocks!", stream_get_contents($temp));
	}

}
