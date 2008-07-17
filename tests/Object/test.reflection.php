<h1>Nette::Object reflection test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette::Debug;*/



class Test extends /*Nette::*/Object
{
	public $a;

	static public $b;

	static function c()
	{}

}



$obj = new Test();

echo "Class: {$obj->Class}\n";

echo "Methods:\n";

Debug::dump($obj->Reflection->getMethods());
