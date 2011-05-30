<?php

/**
 * Common code for Template test cases.
 */

use Nette\Caching\Storages\PhpFileStorage,
	Nette\Templating\Template,
	Nette\Utils\LimitedScope;



// Setup caching storage

class MockCacheStorage extends PhpFileStorage
{

	public function write($key, $data, array $dp)
	{
		file_put_contents(
			__DIR__ . '/compiled/' . trim(str_replace(DIRECTORY_SEPARATOR, '.', $this->hint), '.') . '.php',
			preg_replace("#[a-f0-9]{10,}|(?<=')\w{10,}#", '...', $data)
		);
		return parent::write($key, $data, $dp);
	}

}
