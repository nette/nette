<h1>Nette::Object extension method example</h1>

<pre>
<?php

require_once '../../Nette/loader.php';


interface IFirst {}
interface ISecond extends IFirst {}

function Test_prototype_join(Test $thisObj, $separator)
{
	return __METHOD__ . ' says ' . $thisObj->a . $separator . $thisObj->b;
}



class Test extends /*Nette::*/Object implements ISecond
{
	public $a;
	public $b;


	function __construct($a, $b)
	{
		$this->a = $a;
		$this->b = $b;
	}

}


function Test_join(Test $thisObj, $separator)
{
	return __METHOD__ . ' says ' . $thisObj->a . $separator . $thisObj->b;
}


function IFirst_join(ISecond $thisObj, $separator)
{
	return __METHOD__ . ' says ' . $thisObj->a . $separator . $thisObj->b;
}


function ISecond_join(ISecond $thisObj, $separator)
{
	return __METHOD__ . ' says ' . $thisObj->a . $separator . $thisObj->b;
}



echo "\n\n<h2>Extended method (old way)</h2>\n";

try {
	//Test::extensionMethod(NULL);
	$obj = new Test('Hello', 'World');
	echo '$obj->join: ', $obj->join(' ');

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



echo "\n\n<h2>Extended method</h2>\n";

try {
	Test::extensionMethod('Test::join2', 'Test_join');
	$obj = new Test('Hello', 'World');
	echo '$obj->join2: ', $obj->join2(' ');

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



echo "\n\n<h2>Extended method via interface</h2>\n";

try {
	Test::extensionMethod('IFirst::join3', 'IFirst_join');
	Test::extensionMethod('ISecond::join3', 'ISecond_join');
	$obj = new Test('Hello', 'World');
	echo '$obj->join3: ', $obj->join3(' ');

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



echo "\n\n<h2>Undeclared method</h2>\n";

try {
	echo '$obj->test: ', $obj->test();

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
