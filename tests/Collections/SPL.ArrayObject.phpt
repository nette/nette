<?php

/**
 * Test: SPL ArrayObject basic usage
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
		output('> ' . __METHOD__);
		return parent::exchangeArray($array);
	}

	/**
	 * @return the iterator which is an ArrayIterator object connected to
	 * this object.
	 */
	function getIterator()
	{
		output('> ' . __METHOD__);
		return parent::getIterator();
	}

	/**
	 * @param $index offset to inspect
	 * @return whetehr offset $index esists
	 */
	function offsetExists($index)
	{
		output('> ' . __METHOD__);
		return parent::offsetExists($index);
	}

	/**
	 * @param $index offset to return value for
	 * @return value at offset $index
	 */
	function offsetGet($index)
	{
		output('> ' . __METHOD__);
		return parent::offsetGet($index);
	}

	/**
	 * @param $index index to set
	 * @param $newval new value to store at offset $index
	 */
	function offsetSet($index, $newval)
	{
		output('> ' . __METHOD__);
		return parent::offsetSet($index, $newval);
	}

	/**
	 * @param $index offset to unset
	 */
	function offsetUnset($index)
	{
		output('> ' . __METHOD__);
		return parent::offsetUnset($index);
	}

	/**
	 * @param $value is appended as last element
	 * @warning this method cannot be called when the ArrayObject refers to
	 *          an object.
	 */
	function append($value)
	{
		output('> ' . __METHOD__);
		return parent::append($value);
	}

	/**
	 * @return a \b copy of the array
	 * @note when the ArrayObject refers to an object then this method
	 *       returns an array of the public properties.
	 */
	function getArrayCopy()
	{
		output('> ' . __METHOD__);
		return parent::getArrayCopy();
	}

}



$arr = array(1, 2, 'test', 'null' => NULL);


output('$obj = new TestArrayObject($arr):');
$obj = new TestArrayObject($arr);


output('$obj->append("Mary"):');
$obj->append('Mary');


output('$obj[] = "Jack":');
$obj[] = 'Jack';


output('$obj[array(3)] = "Jack":');
$obj[array(3)] = 'Jack';


output('$obj->offsetSet(array(3), "Jack"):');
$obj->offsetSet(array(3), 'Jack');


output('$obj["a"] = "Jim":');
$obj['a'] = 'Jim';


output('echo $obj["a"]:');
echo $obj['a'];


output('echo $obj["unknown"]:');
echo $obj['unknown'];


output('isset($obj["a"]):');
isset($obj['a']);


output('isset($obj["null"]):');
dump( isset($obj['null']) );


output('unset($obj["a"]):');
unset($obj['a']);




output('count($obj):');
count($obj);




output('dump($obj):');
dump( $obj );


output('$tmp = (array) $obj:');
$tmp = (array) $obj;


output('$tmp = $obj->getArrayCopy():');
$tmp = $obj->getArrayCopy();


output('foreach ($obj as $key => $value):');
foreach ($obj as $key => $value);




output('$obj->exchangeArray($arr):');
$obj->exchangeArray($arr);


output('$obj->ksort():');
$obj->ksort();




output('$obj->setFlags(TestArrayObject::ARRAY_AS_PROPS):');
$obj->setFlags(TestArrayObject::ARRAY_AS_PROPS);


output('$obj->a = "Jack":');
$obj->a = 'Jack';


output('echo $obj->a:');
echo $obj->a;


output('isset($obj->a):');
isset($obj->a);


output('unset($obj->a):');
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


Warning: Illegal offset type %a%
$obj->offsetSet(array(3), "Jack"):

> TestArrayObject::offsetSet


Warning: Illegal offset type %a%
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

bool(TRUE)

unset($obj["a"]):

> TestArrayObject::offsetUnset

count($obj):

dump($obj):

object(TestArrayObject) (6) {
	"0" => int(1)
	"1" => int(2)
	"2" => string(4) "test"
	"null" => NULL
	"3" => string(4) "Mary"
	"4" => string(4) "Jack"
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
