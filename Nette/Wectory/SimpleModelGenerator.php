<?php

class SimpleModelGenerator extends \Nette\Object
{
	private $models;
	
	private function __construct()
	{}
	
	
	public function setModel($name, array $cols)
	{
		$this->models[$name] = $cols;
	}
	
	public function __toString()
	{
		foreach ($this->models as $name => $cols) {
			// implement me
		}
	}
}