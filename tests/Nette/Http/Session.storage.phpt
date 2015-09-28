<?php

/**
 * Test: Nette\Http\Session storage.
 */

use Nette\Object;
use Nette\Http\ISessionStorage;
use Nette\Http\Session;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MySessionStorage extends Object implements ISessionStorage
{
	private $path;

	function open($savePath, $sessionName)
	{
		$this->path = $savePath;
		return TRUE;
	}

	function close()
	{
		return TRUE;
	}

	function read($id)
	{
		return (string) @file_get_contents("$this->path/sess_$id");
	}

	function write($id, $data)
	{
		return (bool) file_put_contents("$this->path/sess_$id", $data);
	}

	function remove($id)
	{
		return !is_file("$this->path/sess_$id") || @unlink("$this->path/sess_$id");
	}

	function clean($maxlifetime)
	{
		foreach (glob("$this->path/sess_*") as $filename) {
			if (filemtime($filename) + $maxlifetime < time()) {
				unlink($filename);
			}
		}
		return TRUE;
	}
}


$session = new Session(new Nette\Http\Request(new Nette\Http\UrlScript), new Nette\Http\Response);

$session->setStorage(new MySessionStorage);
$session->start();
$_COOKIE['PHPSESSID'] = $session->getId();

$namespace = $session->getSection('one');
$namespace->a = 'apple';
$session->close();
unset($_SESSION);

$session->start();
$namespace = $session->getSection('one');
Assert::same('apple', $namespace->a);
