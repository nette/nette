<h1>Nette\Web\Ftp test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Web\Ftp;*/


try {
	$ftp = new Ftp;

	// Opens an FTP connection to the specified host
	$ftp->connect('ftp.nettephp.com');

	// Login with username and password
	$ftp->login('nette@php7.org', 'anonymous');

	// Download file 'README' to local temporary file
	$temp = tmpfile();
	$ftp->fget($temp, 'README', Ftp::ASCII);

	// echo file
	fseek($temp, 0);
	fpassthru($temp);

} catch (FtpException $e) {
	echo 'Error: ', $e->getMessage();
}
