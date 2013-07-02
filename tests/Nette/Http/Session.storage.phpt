<?php

/**
 * Test: Nette\Http\Session storage.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Object,
	Nette\Http\ISessionStorage,
	Nette\Http\Session;


require __DIR__ . '/../bootstrap.php';


ini_set('session.save_path', TEMP_DIR);


class MySessionStorage extends Object implements ISessionStorage
{
	private $path;

	function open($savePath, $sessionName)
	{
		$this->path = $savePath;
	}

	function close()
	{
	}

	function read($id)
	{
		return @file_get_contents("$this->path/sess_$id");
	}

	function write($id, $data)
	{
		return file_put_contents("$this->path/sess_$id", $data);
	}

	function remove($id)
	{
		return @unlink("$this->path/sess_$id");
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


$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$session = $container->getService('session');

$session->setStorage(new MySessionStorage);
$session->start();
