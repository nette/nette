<?php

use Nette\Caching\IStorage;

class TestStorage implements IStorage
{
	private $data = array();

	public function read($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : NULL;
	}

	public function write($key, $data, array $dependencies)
	{
		$this->data[$key] = array(
			'data' => $data,
			'dependencies' => $dependencies,
		);
	}

	public function lock($key) {}

	public function remove($key) {}

	public function clean(array $conditions) {}
}
