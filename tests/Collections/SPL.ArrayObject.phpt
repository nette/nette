<?php

/**
 * Test: Hashtable basic usage
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

/*use Nette\Collections\Hashtable;*/



class TestArrayObject extends ArrayObject
{
	/**
	 * @param $array new array or object
	 */
	function exchangeArray($array)
	{
		message('> ' . __METHOD__);
		return parent::exchangeArray($array);
	}

	/**
	 * @return the iterator which is an ArrayIterator object connected to
	 * this object.
	 */
	function getIterator()
	{
		message('> ' . __METHOD__);
		return parent::getIterator();
	}

	/**
	 * @param $index offset to inspect
	 * @return whetehr offset $index esists
	 */
	function offsetExists($index)
	{
		message('> ' . __METHOD__);
		return parent::offsetExists($index);
	}

	/**
	 * @param $index offset to return value for
	 * @return value at offset $index
	 */
	function offsetGet($index)
	{
		message('> ' . __METHOD__);
		return parent::offsetGet($index);
	}

	/**
	 * @param $index index to set
	 * @param $newval new value to store at offset $index
	 */
	function offsetSet($index, $newval)
	{
		message('> ' . __METHOD__);
		return parent::offsetSet($index, $newval);
	}

	/**
	 * @param $index offset to unset
	 */
	function offsetUnset($index)
	{
		message('> ' . __METHOD__);
		return parent::offsetUnset($index);
	}

	/**
	 * @param $value is appended as last element
	 * @warning this method cannot be called when the ArrayObject refers to
	 *          an object.
	 */
	function append($value)
	{
		message('> ' . __METHOD__);
		return parent::append($value);
	}

	/**
	 * @return a \b copy of the array
	 * @note when the ArrayObject refers to an object then this method
	 *       returns an array of the public properties.
	 */
	function getArrayCopy()
	{
		message('> ' . __METHOD__);
		return parent::getArrayCopy();
	}

}



$arr = array(1, 2, 'test', 'null' => NULL);


message('$obj = new TestArrayObject($arr):');
$obj = new TestArrayObject($arr);


message('$obj->append("Mary"):');
$obj->append('Mary');


message('$obj[] = "Jack":');
$obj[] = 'Jack';


message('$obj[array(3)] = "Jack":');
$obj[array(3)] = 'Jack';


message('$obj->offsetSet(array(3), "Jack"):');
$obj->offsetSet(array(3), 'Jack');


message('$obj["a"] = "Jim":');
$obj['a'] = 'Jim';


message('echo $obj["a"]:');
echo $obj['a'];


message('echo $obj["unknown"]:');
echo $obj['unknown'];


message('isset($obj["a"]):');
isset($obj['a']);


message('isset($obj["null"]):');
var_dump(isset($obj['null']));


message('unset($obj["a"]):');
unset($obj['a']);




message('count($obj):');
count($obj);




message('var_dump($obj):');
var_dump($obj);


message('$tmp = (array) $obj:');
$tmp = (array) $obj;


message('$tmp = $obj->getArrayCopy():');
$tmp = $obj->getArrayCopy();


message('foreach ($obj as $key => $value):');
foreach ($obj as $key => $value);




message('$obj->exchangeArray($arr):');
$obj->exchangeArray($arr);


message('$obj->ksort():');
$obj->ksort();




message('$obj->setFlags(TestArrayObject::ARRAY_AS_PROPS):');
$obj->setFlags(TestArrayObject::ARRAY_AS_PROPS);


message('$obj->a = "Jack":');
$obj->a = 'Jack';


message('echo $obj->a:');
echo $obj->a;


message('isset($obj->a):');
isset($obj->a);


message('unset($obj->a):');
unset($obj->a);



__halt_compiler();

------EXPECT------
$obj = new TestArrayObject($arr):

$obj->append("Mary"):

> TestArrayObject::append

> TestArrayObject::offsetSet

$obj[] = "Jack":

> TestArrayObject::offsetSet

$obj[array(3)] = "Jack":

> TestArrayObject::offsetSet


Warning: Illegal offset type in %a%
$obj->offsetSet(array(3), "Jack"):

> TestArrayObject::offsetSet


Warning: Illegal offset type in %a%
$obj["a"] = "Jim":

> TestArrayObject::offsetSet

echo $obj["a"]:

> TestArrayObject::offsetGet

Jimecho $obj["unknown"]:

> TestArrayObject::offsetGet


Notice: Undefined index:  unknown in %a%
isset($obj["a"]):

> TestArrayObject::offsetExists

isset($obj["null"]):

> TestArrayObject::offsetExists

bool(true)
unset($obj["a"]):

> TestArrayObject::offsetUnset

count($obj):

var_dump($obj):

object(TestArrayObject)#2 (1) {
  ["storage":"ArrayObject":private]=>
  array(6) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    string(4) "test"
    ["null"]=>
    NULL
    [3]=>
    string(4) "Mary"
    [4]=>
    string(4) "Jack"
  }
}
$tmp = (array) $obj:

$tmp = $obj->getArrayCopy():

> TestArrayObject::getArrayCopy

foreach ($obj as $key => $value):

> TestArrayObject::getIterator

$obj->exchangeArray($arr):

> TestArrayObject::exchangeArray

$obj->ksort():

$obj->setFlags(TestArrayObject::ARRAY_AS_PROPS):

$obj->a = "Jack":

> TestArrayObject::offsetSet

echo $obj->a:

> TestArrayObject::offsetGet

Jackisset($obj->a):

> TestArrayObject::offsetExists

unset($obj->a):

> TestArrayObject::offsetUnset
