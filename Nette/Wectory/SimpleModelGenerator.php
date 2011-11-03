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
	
	public function export()
	{
		$export = array();
	
		foreach ($this->models as $name => $cols) {
			$class = new Nette\Utils\PhpGenerator\ClassType($name);
			
			foreach ($cols as $col) {
				$class->setProperty($col);
			}
			
			$export[$name] = $class;
		}
		
		return $export;
	}
}