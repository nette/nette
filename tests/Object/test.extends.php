<h1>Nette::Object extension method example</h1>

<pre>
<?php

require_once '../../Nette/loader.php';



function Test_prototype_join(Test $thisObj, $separator)
{
	return $thisObj->a . $separator . $thisObj->b;
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


function Test__join(Test $thisObj, $separator)
{
	return $thisObj->a . $separator . $thisObj->b;
}


Test::extensionMethod('join2', 'Test__join', 'Test');


echo "\n\n<h2>Extended method (old way)</h2>\n";

try {
	$obj = new Test('Hello', 'World');
	echo '$obj->join: ', $obj->join('***');

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



echo "\n\n<h2>Extended method (new way)</h2>\n";

try {
	$obj = new Test('Hello', 'World');
	echo '$obj->join2: ', $obj->join2('***');

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



echo "\n\n<h2>Undeclared method</h2>\n";

try {
	echo '$obj->test: ', $obj->test();

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
