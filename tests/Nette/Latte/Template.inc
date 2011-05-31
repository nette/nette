<?php

/**
 * Common code for Template test cases.
 */

use Nette\Caching\Storages\PhpFileStorage;



class MockCacheStorage extends PhpFileStorage
{
	public $phtml = array();

	public static function fix($s)
	{
		$s = preg_replace("#[a-f0-9]{10,}|(?<=')\w{10,}#", 'xxx', $s);
		$s = preg_replace('#source file:.*#', '', $s);
		return $s;
	}

	public function __construct()
	{
		parent::__construct(TEMP_DIR);
	}

	public function read($key)
	{
		return NULL;
	}

	public function write($key, $data, array $dp)
	{
		$this->phtml[basename($this->hint)] = self::fix($data);
	}

}
