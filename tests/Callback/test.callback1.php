<h1>Nette::Callback test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Callback;*/
/*use Nette::Debug;*/

function myFunc($a, $b)
{
	$method = __METHOD__;
	return "Hello $a and $b from $method\n";
}


class myClass
{

	function myFunc($a, $b)
	{
		$method = __METHOD__;
		return "Hello $a and $b from $method\n";
	}


	function mySimpleFunc()
	{
		$method = __METHOD__;
		echo "Hello from $method\n";
	}


	static function myStaticFunc($a, $b)
	{
		$method = __METHOD__;
		return "Hello $a and $b from $method\n";
	}

}



echo "new Callback('myFunc')\n";
$callback = new Callback('myFunc');

echo "invoke(...)\n";
echo $callback->invoke('Jim', 'Mary');

echo "invokeArgs(...)\n";
echo $callback->invokeArgs(array('Jim', 'Mary'));

echo "isCallable?\n";
Debug::dump($callback->isCallable());

echo "getNative()\n";
Debug::dump($callback->getNative());


echo "\n";
echo "new Callback('myClass', 'myStaticFunc')\n";
$callback = new Callback('myClass', 'myStaticFunc');
echo $callback->invoke('Jim', 'Mary');



echo "\n";
$obj = new myClass;
echo "new Callback(\$obj, 'myFunc')\n";
$callback = new Callback($obj, 'myFunc');
echo $callback->invoke('Jim', 'Mary');


echo "\n";
$nativeCallback = array($obj, 'mySimpleFunc');
echo "new Callback(\$nativeCallback)\n";
$callback = new Callback($nativeCallback);
$callback->invoke();

echo "check getNative\n";
Debug::dump($callback->getNative() === $nativeCallback);

echo "toString:\n";
echo strtoupper($callback);
