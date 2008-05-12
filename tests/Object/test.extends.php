<?php

require_once '../../Nette/loader.php';

echo "<h1>Nette::Object extension method example</h1>";
echo "<pre>\n";



function Test_prototype_join(Test $ths, $separator)
{
	return $ths->a . $separator . $ths->b;
}



class Test extends /*Nette::*/Object
{
	public $a;
	public $b;


	function __construct($a, $b)
	{
		$this->a = $a;
		$this->b = $b;
	}

}



echo "\n\n<h2>Extended method</h2>\n";

try {
	$obj = new Test('Hello', 'World');
	echo '$obj->join: ', $obj->join('***');

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



echo "\n\n<h2>Undeclared method</h2>\n";

try {
	echo '$obj->test: ', $obj->test();

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
