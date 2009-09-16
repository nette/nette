<?php

/**
 * Common code for collections test cases.
 */



class Person
{
	private $name;


	public function __construct($name)
	{
		$this->name = $name;
	}



	public function sayHi()
	{
		output("My name is $this->name");
	}



	public function __toString()
	{
		return $this->name;
	}
}

