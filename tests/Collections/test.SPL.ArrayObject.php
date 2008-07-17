<h1>SPL::ArrayObject test</h1>

<pre>
<?php

class Test extends ArrayObject
{
	/**
	 * @param $array new array or object
	 */
	function exchangeArray($array)
	{
		echo " > ", __METHOD__, "\n";
		return parent::exchangeArray($array);
	}

	/**
	 * @return the iterator which is an ArrayIterator object connected to
	 * this object.
	 */
	function getIterator()
	{
		echo " > ", __METHOD__, "\n";
		return parent::getIterator();
	}

	/**
	 * @param $index offset to inspect
	 * @return whetehr offset $index esists
	 */
	function offsetExists($index)
	{
		echo " > ", __METHOD__, "\n";
		return parent::offsetExists($index);
	}

	/**
	 * @param $index offset to return value for
	 * @return value at offset $index
	 */
	function offsetGet($index)
	{
		echo " > ", __METHOD__, "\n";
		return parent::offsetGet($index);
	}

	/**
	 * @param $index index to set
	 * @param $newval new value to store at offset $index
	 */
	function offsetSet($index, $newval)
	{
		echo " > ", __METHOD__, "\n";
		return parent::offsetSet($index, $newval);
	}

	/**
	 * @param $index offset to unset
	 */
	function offsetUnset($index)
	{
		echo " > ", __METHOD__, "\n";
		return parent::offsetUnset($index);
	}

	/**
	 * @param $value is appended as last element
	 * @warning this method cannot be called when the ArrayObject refers to
	 *          an object.
	 */
	function append($value)
	{
		echo " > ", __METHOD__, "\n";
		return parent::append($value);
	}

	/**
	 * @return a \b copy of the array
	 * @note when the ArrayObject refers to an object then this method
	 *       returns an array of the public properties.
	 */
	function getArrayCopy()
	{
		echo " > ", __METHOD__, "\n";
		return parent::getArrayCopy();
	}

}



$arr = array(1, 2, 'test', 'null' => NULL);


echo '$obj = new Test($arr):', "\n";
$obj = new Test($arr);


echo '$obj->append("Mary"):', "\n";
$obj->append('Mary');


echo '$obj[] = "Jack":', "\n";
$obj[] = 'Jack';


echo '$obj[array(3)] = "Jack":', "\n";
$obj[array(3)] = 'Jack';


echo '$obj->offsetSet(array(3), "Jack"):', "\n";
$obj->offsetSet(array(3), 'Jack');


echo '$obj["a"] = "Jim":', "\n";
$obj['a'] = 'Jim';


echo 'echo $obj["a"]:', "\n";
echo $obj['a'];


echo 'echo $obj["unknown"]:', "\n";
echo $obj['unknown'];


echo 'isset($obj["a"]):', "\n";
isset($obj['a']);


echo 'isset($obj["null"]):', "\n";
var_dump(isset($obj['null']));


echo 'unset($obj["a"]):', "\n";
unset($obj['a']);




echo 'count($obj):', "\n";
count($obj);




echo 'var_dump($obj):', "\n";
var_dump($obj);


echo '$tmp = (array) $obj:', "\n";
$tmp = (array) $obj;


echo '$tmp = $obj->getArrayCopy():', "\n";
$tmp = $obj->getArrayCopy();


echo 'foreach ($obj as $key => $value):', "\n";
foreach ($obj as $key => $value);




echo '$obj->exchangeArray($arr):', "\n";
$obj->exchangeArray($arr);


echo '$obj->ksort():', "\n";
$obj->ksort();




echo '$obj->setFlags(Test::ARRAY_AS_PROPS):', "\n";
$obj->setFlags(Test::ARRAY_AS_PROPS);


echo '$obj->a = "Jack":', "\n";
$obj->a = 'Jack';


echo 'echo $obj->a:', "\n";
echo $obj->a;


echo 'isset($obj->a):', "\n";
isset($obj->a);


echo 'unset($obj->a):', "\n";
unset($obj->a);
