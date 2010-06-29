<?php

/**
 * Test: SPL ArrayObject basic usage
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

require __DIR__ . '/../initialize.php';

use Nette\Collections\Hashtable;



class TestArrayObject extends ArrayObject
{
	/**
	 * @param $array new array or object
	 */
	function exchangeArray($array)
	{
		T::note('> ' . __METHOD__);
		return parent::exchangeArray($array);
	}

	/**
	 * @return the iterator which is an ArrayIterator object connected to
	 * this object.
	 */
	function getIterator()
	{
		T::note('> ' . __METHOD__);
		return parent::getIterator();
	}

	/**
	 * @param $index offset to inspect
	 * @return whetehr offset $index esists
	 */
	function offsetExists($index)
	{
		T::note('> ' . __METHOD__);
		return parent::offsetExists($index);
	}

	/**
	 * @param $index offset to return value for
	 * @return value at offset $index
	 */
	function offsetGet($index)
	{
		T::note('> ' . __METHOD__);
		return parent::offsetGet($index);
	}

	/**
	 * @param $index index to set
	 * @param $newval new value to store at offset $index
	 */
	function offsetSet($index, $newval)
	{
		T::note('> ' . __METHOD__);
		return parent::offsetSet($index, $newval);
	}

	/**
	 * @param $index offset to unset
	 */
	function offsetUnset($index)
	{
		T::note('> ' . __METHOD__);
		return parent::offsetUnset($index);
	}

	/**
	 * @param $value is appended as last element
	 * @warning this method cannot be called when the ArrayObject refers to
	 *          an object.
	 */
	function append($value)
	{
		T::note('> ' . __METHOD__);
		return parent::append($value);
	}

	/**
	 * @return a \b copy of the array
	 * @note when the ArrayObject refers to an object then this method
	 *       returns an array of the public properties.
	 */
	function getArrayCopy()
	{
		T::note('> ' . __METHOD__);
		return parent::getArrayCopy();
	}

}



$arr = array(1, 2, 'test', 'null' => NULL);


T::note('$obj = new TestArrayObject($arr):');
$obj = new TestArrayObject($arr);


T::note('$obj->append("Mary"):');
$obj->append('Mary');


T::note('$obj[] = "Jack":');
$obj[] = 'Jack';


T::note('$obj[array(3)] = "Jack":');
$obj[array(3)] = 'Jack';


T::note('$obj->offsetSet(array(3), "Jack"):');
$obj->offsetSet(array(3), 'Jack');


T::note('$obj["a"] = "Jim":');
$obj['a'] = 'Jim';


T::note('echo $obj["a"]:');
echo $obj['a'];


T::note('echo $obj["unknown"]:');
echo $obj['unknown'];


T::note('isset($obj["a"]):');
isset($obj['a']);


T::note('isset($obj["null"]):');
T::dump( isset($obj['null']) );


T::note('unset($obj["a"]):');
unset($obj['a']);



T::note('count($obj):');
count($obj);



T::note('T::dump($obj):');
T::dump( $obj );


T::note('$tmp = (array) $obj:');
$tmp = (array) $obj;


T::note('$tmp = $obj->getArrayCopy():');
$tmp = $obj->getArrayCopy();


T::note('foreach ($obj as $key => $value):');
foreach ($obj as $key => $value);



T::note('$obj->exchangeArray($arr):');
$obj->exchangeArray($arr);


T::note('$obj->ksort():');
$obj->ksort();



T::note('$obj->setFlags(TestArrayObject::ARRAY_AS_PROPS):');
$obj->setFlags(TestArrayObject::ARRAY_AS_PROPS);


T::note('$obj->a = "Jack":');
$obj->a = 'Jack';


T::note('echo $obj->a:');
echo $obj->a;


T::note('isset($obj->a):');
isset($obj->a);


T::note('unset($obj->a):');
unset($obj->a);



__halt_compiler() ?>

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

T::dump($obj):

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
