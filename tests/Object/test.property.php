<?php

require_once '../../Nette/loader.php';

/*use Nette::Debug;*/

echo '<h1>Nette::Object property test</h1>';
echo "<pre>\n";




class Test extends /*Nette::*/Object
{
	private $name;
	private $items;


	function __construct()
	{
		$this->items = new ArrayObject;
	}



	public function getName()
	{
		return $this->name;
	}



	public function setName($name)
	{
		$this->name = $name;
	}



	public function getItems()
	{
		return $this->items;
	}



	public function setItems(array $value)
	{
		$this->items = new ArrayObject($value);
	}



	public function getReadOnly()
	{
		return 'OK';
	}



	public function gets() // or setupXyz, settle...
	{
		echo __METHOD__;
		return 'ERROR';
	}



	public function export()
	{
		Debug::dump($this);
	}
}



echo "\n\n<h2>String property</h2>\n";

$obj = new Test;
$obj->name = 'hello';
$obj->name .= ' worlds';
echo '$obj->name = ', $obj->name, "\n";
echo '$obj->Name = ', $obj->Name, "\n\n";


echo "\n\n<h2>Array property</h2>\n";

$obj->items[] = 'test';
$obj->items = array();
$obj->items[] = 'one';
$obj->items[] = 'two';
echo $obj->items[1], "\n\n";
$obj->export();



echo "\n\n<h2>Reference test</h2>\n";

$x = & $obj->name;
$x = 'changed by reference';
echo $obj->name, "\n\n";



echo "\n\n<h2>Read-only property</h2>\n";

try {
	echo 'read: ', $obj->readOnly, "\n";

	echo 'write: ';
	$obj->readOnly = 'value';

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



echo "\n\n<h2>Undeclared property</h2>\n";

try {
	echo 'read: ';
	$val = $obj->s;

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

echo "\n";

try {
	echo 'write: ';
	$obj->S = 'value';

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
echo "\n\n";
