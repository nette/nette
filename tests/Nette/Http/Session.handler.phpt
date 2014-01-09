<?php

/**
 * Test: Nette\Http\Session storage.
 *
 * @author     David Grudl
 * @phpversion 5.4
 */

use Nette\Object,
	Nette\Http\Session,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MySessionStorage extends Object implements SessionHandlerInterface
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

	function destroy($id)
	{
		return @unlink("$this->path/sess_$id");
	}

	function gc($maxlifetime)
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

$session->setHandler(new MySessionStorage);
$session->start();
