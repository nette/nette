<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\Annotations;*/
/*use Nette\Debug;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @title(value ="Johno's addendum", mode=True)
 * @title( value= 'One, Two', mode= true or false)
 * @title( value = 'Three (Four)', mode = 'false')
 * @components(item 1)
 * @persistent(true)
 * @persistent(FALSE)
 * @renderable
 */
class TestClass {

	/** @secured(role = "admin", level = 2) */
	public $foo;

	/** @RolesAllowed('admin', web editor) */
	public function bar()
	{}

}



/**
 * @package    Nette
 * @subpackage UnitTests
 */
class NetteAnnotationsTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Class annotations test.
	 * @return void
	 */
	public function testClassAnnotations()
	{
		$rc = new ReflectionClass('TestClass');
		$tmp = Annotations::getAll($rc);

		$this->assertEquals("Johno's addendum", $tmp["title"][0]->value);
		$this->assertTrue($tmp["title"][0]->mode);
		$this->assertEquals("One, Two", $tmp["title"][1]->value);
		$this->assertEquals("true or false", $tmp["title"][1]->mode);
		$this->assertEquals("Three (Four)", $tmp["title"][2]->value);
		$this->assertEquals("false", $tmp["title"][2]->mode);
		$this->assertEquals("item 1", $tmp["components"][0]);
		$this->assertTrue($tmp["persistent"][0]);
		$this->assertFalse($tmp["persistent"][1]);
		$this->assertTrue($tmp["renderable"][0]);

		$this->assertSame($tmp, Annotations::getAll($rc), 'cache test');
		$this->assertNotSame($tmp, Annotations::getAll(new ReflectionClass('ReflectionClass')), 'cache test');

		$this->assertTrue(Annotations::has($rc, 'title'), "has('title')");
		$this->assertEquals("Three (Four)", Annotations::get($rc, 'title')->value);
		$this->assertEquals("false", Annotations::get($rc, 'title')->mode);

		$tmp = Annotations::getAll($rc, 'title');
		$this->assertEquals("Johno's addendum", $tmp[0]->value);
		$this->assertTrue($tmp[0]->mode);
		$this->assertEquals("One, Two", $tmp[1]->value);
		$this->assertEquals("true or false", $tmp[1]->mode);
		$this->assertEquals("Three (Four)", $tmp[2]->value);
		$this->assertEquals("false", $tmp[2]->mode);

		$this->assertTrue(Annotations::has($rc, 'renderable'), "has('renderable')");
		$this->assertTrue(Annotations::get($rc, 'renderable'), "get('renderable')");
		$tmp = Annotations::getAll($rc, 'renderable');
		$this->assertTrue($tmp[0]);
		$tmp = Annotations::getAll($rc, 'persistent');
		$this->assertFalse(Annotations::get($rc, 'persistent'), "get('persistent')");
		$this->assertTrue($tmp[0]);
		$this->assertFalse($tmp[1]);

		$this->assertFalse(Annotations::has($rc, 'xxx'), "has('xxx')");
		$this->assertNull(Annotations::get($rc, 'xxx'), "get('xxx')");
	}



	/**
	 * Property annotations test.
	 * @return void
	 */
	public function testPropertyAnnotations()
	{
		$rp = new ReflectionProperty('TestClass', 'foo');
		$tmp = Annotations::getAll($rp);

		$this->assertEquals("admin", $tmp["secured"][0]->role);
		$this->assertEquals(2, $tmp["secured"][0]->level);
	}



	/**
	 * Method annotations test.
	 * @return void
	 */
	public function testMethodAnnotations()
	{
		$rm = new ReflectionMethod('TestClass', 'bar');
		$tmp = Annotations::getAll($rm);

		$this->assertEquals('admin', $tmp["RolesAllowed"][0][0]);
		$this->assertEquals('web editor', $tmp["RolesAllowed"][0][1]);
	}

}
