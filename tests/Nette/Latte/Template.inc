<?php

/**
 * Common code for Template test cases.
 */

use Nette\Caching\Storages\PhpFileStorage;


class MockCacheStorage extends PhpFileStorage
{
	public $phtml = array();

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
		$this->phtml[basename($this->hint)] = codefix($data);
	}

}


function codefix($s)
{
	$s = preg_replace("#(?<=['_])[a-z0-9]{10,}(?=['_])#", 'xxx', $s);
	$s = preg_replace('#source file:.*#', '', $s);
	return $s;
}
