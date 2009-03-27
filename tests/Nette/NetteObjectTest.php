<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 * @version    $Id$
 */

/*use Nette\Debug, Nette\Object;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';




interface IFirst {}

interface ISecond extends IFirst {}


class TestClass extends Object implements ISecond
{

	private $foo, $bar;

	private $items;

	private $onPrivate;

	protected $onProtected;

	public $onPublic;

	public $onPublic2 = 'nonevent';

	static public $onPublicStatic;



	function __construct($foo = NULL, $bar = NULL)
	{
		$this->foo = $foo;
		$this->bar = $bar;
		$this->items = new ArrayObject;
	}



	public function getFoo()
	{
		return $this->foo;
	}



	public function setFoo($foo)
	{
		$this->foo = $foo;
	}



	public function getBar()
	{
		return $this->bar;
	}



	public function getItems()
	{
		return $this->items;
	}



	public function setItems(array $value)
	{
		$this->items = new ArrayObject($value);
	}



	public function gets() // or setupXyz, settle...
	{
		echo __METHOD__;
		return 'ERROR';
	}

}



function TestClass_prototype_oldJoin(TestClass $_this, $separator)
{
	return __METHOD__ . ' says ' . $_this->foo . $separator . $_this->bar;
}


function TestClass_join(TestClass $_this, $separator)
{
	return __METHOD__ . ' says ' . $_this->foo . $separator . $_this->bar;
}


function IFirst_join(ISecond $_this, $separator)
{
	return __METHOD__ . ' says ' . $_this->foo . $separator . $_this->bar;
}


function ISecond_join(ISecond $_this, $separator)
{
	return __METHOD__ . ' says ' . $_this->foo . $separator . $_this->bar;
}




function handler($obj)
{
	$obj->counter++;
}


class Handler
{
	function __invoke($obj)
	{
		$obj->counter++;
	}
}







/**
 * @package    Nette
 * @subpackage UnitTests
 */
class NetteObjectTest extends PHPUnit_Framework_TestCase
{


	/**
	 * Undeclared property reading test.
	 * @return void
	 */
	public function testUndeclaredPropertyReading()
	{
		$this->setExpectedException("MemberAccessException", "Cannot read an undeclared property TestClass::\$undeclared.", 0);
		$obj = new TestClass;
		$this->assertFalse(isset($obj->undeclared));
		$val = $obj->undeclared;
	}



	/**
	 * Undeclared property writing test.
	 * @return void
	 */
	public function testUndeclaredPropertyWriting()
	{
		$this->setExpectedException("MemberAccessException", "Cannot assign to an undeclared property TestClass::\$undeclared.", 0);
		$obj = new TestClass;
		$obj->undeclared = 'value';
	}



	/**
	 * Undeclared method test.
	 * @return void
	 */
	public function testUndeclaredMethod()
	{
		$this->setExpectedException("MemberAccessException", "Call to undefined method TestClass::undeclared().", 0);
		$obj = new TestClass;
		$obj->undeclared();
	}



	/**
	 * getClass method test.
	 * @return void
	 */
	public function testClass()
	{
		$obj = new TestClass;
		$this->assertEquals("TestClass", $obj->getClass());
		$this->assertEquals("TestClass", $obj->class);

		if (version_compare(PHP_VERSION , '5.3.0', '>=')) {
			$this->assertEquals("TestClass", Test::getClass());
			$class = 'TestClass';
			$this->assertEquals("TestClass", eval('return $class::getClass();'));
			//$this->assertEquals("TestClass", $class::getClass());
		}
	}



	/**
	 * getClass in PHP 5.3 method test.
	 * @return void
	 */
	public function testClass53()
	{
		if (version_compare(PHP_VERSION , '5.3.0', '<')) return;

		$this->assertEquals("TestClass", Test::getClass());
		$class = 'TestClass';
		$this->assertEquals("TestClass", eval('return $class::getClass();'));
		//$this->assertEquals("TestClass", $class::getClass());
	}



	/**
	 * Reflection test.
	 * @return void
	 */
	public function testReflection()
	{
		$obj = new TestClass;
		$this->assertEquals("TestClass", $obj->getReflection()->getName());
		$this->assertEquals("TestClass", $obj->Reflection->getName());
	}



	/**
	 * String property test.
	 * @return void
	 */
	public function testStringProperty()
	{
		$obj = new TestClass;
		$obj->foo = 'hello';
		$obj->foo .= ' world';
		$this->assertEquals("hello world", $obj->foo);
		$this->assertEquals("hello world", $obj->Foo);
	}



	/**
	 * Array property test.
	 * @return void
	 */
	public function testArrayProperty()
	{
		$obj = new TestClass;
		$obj->items[] = 'test';
		$this->assertEquals("test", $obj->items[0]);

		$obj->items = array();
		$obj->items[] = 'one';
		$obj->items[] = 'two';
		$this->assertEquals("one", $obj->items[0]);
		$this->assertEquals("two", $obj->items[1]);
	}



	/**
	 * Reference property test test.
	 * @return void
	 */
	public function testReferencePropertyTest()
	{
		$obj = new TestClass;
		$obj->foo = 'hello';
		@$x = & $obj->foo;
		$x = 'changed by reference';
		$this->assertEquals("hello", $obj->foo);
	}



	/**
	 * Read-only property test.
	 * @return void
	 */
	public function testReadOnlyProperty()
	{
		$this->setExpectedException("MemberAccessException", "Cannot assign to a read-only property TestClass::\$bar.", 0);

		$obj = new TestClass('Hello', 'World');
		$this->assertEquals("World", $obj->bar);

		$obj->bar = 'value';
	}



	/**
	 * Extended method (old way) test.
	 * @return void
	 */
	public function testExtendedMethodOldWay()
	{
		$obj = new TestClass('Hello', 'World');
		$this->assertEquals("TestClass_prototype_oldJoin says Hello World", $obj->oldJoin(" "));
	}



	/**
	 * Extended method test.
	 * @return void
	 */
	public function testExtendedMethod()
	{
		TestClass::extensionMethod('TestClass::join', 'TestClass_join');
		$obj = new TestClass('Hello', 'World');
		$this->assertEquals("TestClass_join says Hello World", $obj->join(" "));
	}



	/**
	 * Extended method via interface test.
	 * @return void
	 */
	public function testExtendedMethodViaInterface()
	{
		TestClass::extensionMethod('IFirst::joinI', 'IFirst_join');
		TestClass::extensionMethod('ISecond::joinI', 'ISecond_join');
		$obj = new TestClass('Hello', 'World');
		$this->assertEquals("ISecond_join says Hello World", $obj->joinI(" "));
	}



	/**
	 * Extended method in PHP 5.3 test.
	 * @return void
	 */
	public function testExtendedMethod53()
	{
		if (version_compare(PHP_VERSION , '5.3.0', '<')) return;

		eval('
		TestClass::extensionMethod("join53",
			function (TestClass $_this, $separator) {
				return $_this->foo . $separator . $_this->bar;
			}
		);
		');

		$obj = new TestClass('Hello', 'World');
		$this->assertEquals("Hello*World", $obj->join53("*"));
	}



	/**
	 * Invoking events test.
	 * @return void
	 */
	public function testInvokingEvents()
	{
		$obj = new TestClass;

		try {
			// private
			$obj->onPrivate(123);
			$this->fail('called private event');
		} catch (MemberAccessException $e) {
			$this->assertEquals("Call to undefined method TestClass::onPrivate().", $e->getMessage());
		}

		try {
			// protected
			$obj->onProtected(123);
			$this->fail('called protected event');
		} catch (MemberAccessException $e) {
			$this->assertEquals("Call to undefined method TestClass::onProtected().", $e->getMessage());
		}

		try {
			// public
			$obj->onPublic(123);
		} catch (MemberAccessException $e) {
			$this->fail('failed public event');
		}

		try {
			// public nonarray
			$obj->onPublic2(123);
		} catch (MemberAccessException $e) {
			$this->fail('failed public nonarray event');
		}

		try {
			// public static
			$obj->onPublicStatic(123);
			$this->fail('called public static event');
		} catch (MemberAccessException $e) {
			$this->assertEquals("Call to undefined method TestClass::onPublicStatic().", $e->getMessage());
		}
	}



	/**
	 * Attaching handler test.
	 * @return void
	 */
	public function testAttachingHandler()
	{
		$obj = new TestClass;
		$var = (object) NULL;

		$obj->onPublic[] = 'handler';

		$obj->onPublic($var);
		$this->assertEquals(1, $var->counter);


		$obj->onPublic[] = new Handler;

		$obj->onPublic($var);
		$this->assertEquals(3, $var->counter);
	}

}
