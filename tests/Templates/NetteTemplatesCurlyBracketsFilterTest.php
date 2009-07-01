<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 * @version    $Id$
 */

/*use Nette\Debug;*/
/*use Nette\Templates\CurlyBracketsFilter;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @package    Nette\Templates
 * @subpackage UnitTests
 */
class NetteTemplatesCurlyBracketsFilter extends PHPUnit_Framework_TestCase
{

	/**
	 * formatArray() test.
	 * @return void
	 */
	public function testFormatArray()
	{
		// symbols
		$this->assertEquals('', CurlyBracketsFilter::formatArray(''));
		$this->assertEquals('', CurlyBracketsFilter::formatArray('', '&'));
		$this->assertEquals('array(1)', CurlyBracketsFilter::formatArray('1'));
		$this->assertEquals('&array(1)', CurlyBracketsFilter::formatArray('1', '&'));
		$this->assertEquals("array('symbol')", CurlyBracketsFilter::formatArray('symbol'));
		$this->assertEquals("array(1, 2,'symbol1','symbol2')", CurlyBracketsFilter::formatArray('1, 2, symbol1, symbol2'));

		// strings
		$this->assertEquals('array("\"1, 2, symbol1, symbol2")', CurlyBracketsFilter::formatArray('"\"1, 2, symbol1, symbol2"')); // unable to parse "${'"'}" yet
		$this->assertEquals("array('\\'1, 2, symbol1, symbol2')", CurlyBracketsFilter::formatArray("'\\'1, 2, symbol1, symbol2'"));
		$this->assertEquals("array('\\\\'1, 2,'symbol1', symbol2')", CurlyBracketsFilter::formatArray("'\\\\'1, 2, symbol1, symbol2'"));

		// key words
		$this->assertEquals('array(TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class)', CurlyBracketsFilter::formatArray('TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class'));
		$this->assertEquals('array(func (10))', CurlyBracketsFilter::formatArray('func (10)'));

		// associative arrays
		$this->assertEquals("array('symbol1' =>'value','symbol2'=>'value')", CurlyBracketsFilter::formatArray('symbol1 => value,symbol2=>value'));
		$this->assertEquals("array('symbol1' => array ('symbol2' =>'value'))", CurlyBracketsFilter::formatArray('symbol1 => array (symbol2 => value)'));

		// equal signs
		$this->assertEquals("array('symbol1' =>'value','symbol2'=>'value')", CurlyBracketsFilter::formatArray('symbol1 = value,symbol2=value'));
		$this->assertEquals('array($x == 1, $x != 1)', CurlyBracketsFilter::formatArray('$x == 1, $x != 1'));
	}



	/**
	 * formatModifiers() test.
	 * @return void
	 */
	public function testFormatModifiers()
	{
		// special
		$this->assertEquals('@', CurlyBracketsFilter::formatModifiers('@', ''));
		$this->assertEquals('@', CurlyBracketsFilter::formatModifiers('@', ':'));
		$this->assertEquals('@', CurlyBracketsFilter::formatModifiers('@', '|'));
		$this->assertEquals('$template->mod(@)', CurlyBracketsFilter::formatModifiers('@', 'mod::||:|'));

		// common
		$this->assertEquals('$template->mod(@)', CurlyBracketsFilter::formatModifiers('@', 'mod'));
		$this->assertEquals('$template->mod3($template->mod2($template->mod1(@)))', CurlyBracketsFilter::formatModifiers('@', 'mod1|mod2|mod3'));

		// arguments
		$this->assertEquals('$template->mod(@, \'arg1\', 2, $var["pocet"])', CurlyBracketsFilter::formatModifiers('@', 'mod:arg1:2:$var["pocet"]'));
		$this->assertEquals('$template->mod(@, \'arg1\', 2, $var["pocet"])', CurlyBracketsFilter::formatModifiers('@', 'mod,arg1,2,$var["pocet"]'));
		$this->assertEquals('$template->mod(@, " :a:b:c", "", 3, "")', CurlyBracketsFilter::formatModifiers('@', 'mod:" :a:b:c":"":3:""'));
		$this->assertEquals('$template->mod(@, "\":a:b:c")', CurlyBracketsFilter::formatModifiers('@', 'mod:"\\":a:b:c"'));
		$this->assertEquals("\$template->mod(@, '\':a:b:c')", CurlyBracketsFilter::formatModifiers('@', "mod:'\\':a:b:c'"));
		$this->assertEquals("\$template->mod(@, '\\\\', 'a', 'b', 'c', 'arg2')", CurlyBracketsFilter::formatModifiers('@', "mod:'\\\\':a:b:c':arg2"));
	}

}
