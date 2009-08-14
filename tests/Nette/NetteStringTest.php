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

/*use Nette\Debug;*/
/*use Nette\String;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @package    Nette
 * @subpackage UnitTests
 */
class NetteStringTest extends PHPUnit_Framework_TestCase
{

	/**
	 * startsWith test.
	 * @return void
	 */
	public function testStartswith()
	{
		$this->assertTrue(String::startsWith('123', NULL), "String::startsWith('123', NULL)");
		$this->assertTrue(String::startsWith('123', ''), "String::startsWith('123', '')");
		$this->assertTrue(String::startsWith('123', '1'), "String::startsWith('123', '1')");
		$this->assertFalse(String::startsWith('123', '2'), "String::startsWith('123', '2')");
		$this->assertTrue(String::startsWith('123', '123'), "String::startsWith('123', '123')");
		$this->assertFalse(String::startsWith('123', '1234'), "String::startsWith('123', '1234')");
	}



	/**
	 * endsWith test.
	 * @return void
	 */
	public function testEndswith()
	{
		$this->assertTrue(String::endsWith('123', NULL), "String::endsWith('123', NULL)");
		$this->assertTrue(String::endsWith('123', ''), "String::endsWith('123', '')");
		$this->assertTrue(String::endsWith('123', '3'), "String::endsWith('123', '3')");
		$this->assertFalse(String::endsWith('123', '2'), "String::endsWith('123', '2')");
		$this->assertTrue(String::endsWith('123', '123'), "String::endsWith('123', '123')");
		$this->assertFalse(String::endsWith('123', '1234'), "String::endsWith('123', '1234')");
	}



	/**
	 * webalize test.
	 * @return void
	 */
	public function testWebalize()
	{
		$this->assertEquals("zlutoucky-kun-oooo", String::webalize("&\xc5\xbdLU\xc5\xa4OU\xc4\x8cK\xc3\x9d K\xc5\xae\xc5\x87 \xc3\xb6\xc5\x91\xc3\xb4o!")); // &ŽLUŤOUČKÝ KŮŇ öőôo!
		$this->assertEquals("ZLUTOUCKY-KUN-oooo", String::webalize("&\xc5\xbdLU\xc5\xa4OU\xc4\x8cK\xc3\x9d K\xc5\xae\xc5\x87 \xc3\xb6\xc5\x91\xc3\xb4o!", NULL, FALSE)); // &ŽLUŤOUČKÝ KŮŇ öőôo!
		$this->assertEquals("1-4-!", String::webalize("\xc2\xBC!", '!'));
	}



	/**
	 * normalize test.
	 * @return void
	 */
	public function testNormalize()
	{
		$this->assertEquals("Hello\n  World", String::normalize("\r\nHello  \r  World \n\n"));
	}



	/**
	 * checkEncoding test.
	 * @return void
	 */
	public function testCheckEncoding()
	{
		$this->assertTrue(String::checkEncoding("\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd"), 'UTF-8'); // žluťoučký
		$this->assertTrue(String::checkEncoding("\x01"), 'C0');
		$this->assertFalse(String::checkEncoding("\xed\xa0\x80"), 'surrogate pairs'); // xD800
		$this->assertFalse(String::checkEncoding("\xef\xbb\xbf"), 'noncharacter'); // xFEFF
		$this->assertFalse(String::checkEncoding("\xf4\x90\x80\x80"), 'out of range'); // x110000
	}



	/**
	 * fixEncoding test.
	 * @return void
	 */
	public function testFixEncoding()
	{
		$this->assertEquals("\xc5\xbea\x01bcde", String::fixEncoding("\xc5\xbea\x01b\xed\xa0\x80c\xef\xbb\xbfd\xf4\x90\x80\x80e"), 'C0'); // C0, surrogate pairs, noncharacter, out of range
	}



	/**
	 * chr test.
	 * @return void
	 */
	public function testChr()
	{
		$this->assertEquals("\x00", String::chr(0), '#0');
		$this->assertEquals("\xc3\xbf", String::chr(255), '#255 in UTF-8');
		$this->assertEquals("\xFF", String::chr(255, 'ISO-8859-1'), '#255 in ISO-8859-1');
	}



	/**
	 * truncate test.
	 * @return void
	 */
	public function testTruncate()
	{
		iconv_set_encoding('internal_encoding', 'UTF-8');
		$s = "\xc5\x98ekn\xc4\x9bte, jak se (dnes) m\xc3\xa1te?"; // Řekněte, jak se (dnes) máte?

		$this->assertEquals("\xe2\x80\xa6", String::truncate($s, -1), "length=-1");
		$this->assertEquals("\xe2\x80\xa6", String::truncate($s, 0), "length=0");
		$this->assertEquals("\xe2\x80\xa6", String::truncate($s, 1), "length=1");
		$this->assertEquals("\xc5\x98\xe2\x80\xa6", String::truncate($s, 2), "length=2");
		$this->assertEquals("\xc5\x98e\xe2\x80\xa6", String::truncate($s, 3), "length=3");
		$this->assertEquals("\xc5\x98ek\xe2\x80\xa6", String::truncate($s, 4), "length=4");
		$this->assertEquals("\xc5\x98ekn\xe2\x80\xa6", String::truncate($s, 5), "length=5");
		$this->assertEquals("\xc5\x98ekn\xc4\x9b\xe2\x80\xa6", String::truncate($s, 6), "length=6");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bt\xe2\x80\xa6", String::truncate($s, 7), "length=7");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte\xe2\x80\xa6", String::truncate($s, 8), "length=8");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte,\xe2\x80\xa6", String::truncate($s, 9), "length=9");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte,\xe2\x80\xa6", String::truncate($s, 10), "length=10");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte,\xe2\x80\xa6", String::truncate($s, 11), "length=11");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte,\xe2\x80\xa6", String::truncate($s, 12), "length=12");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak\xe2\x80\xa6", String::truncate($s, 13), "length=13");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak\xe2\x80\xa6", String::truncate($s, 14), "length=14");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak\xe2\x80\xa6", String::truncate($s, 15), "length=15");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se\xe2\x80\xa6", String::truncate($s, 16), "length=16");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se \xe2\x80\xa6", String::truncate($s, 17), "length=17");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se \xe2\x80\xa6", String::truncate($s, 18), "length=18");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se \xe2\x80\xa6", String::truncate($s, 19), "length=19");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se \xe2\x80\xa6", String::truncate($s, 20), "length=20");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se \xe2\x80\xa6", String::truncate($s, 21), "length=21");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se (dnes\xe2\x80\xa6", String::truncate($s, 22), "length=22");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se (dnes)\xe2\x80\xa6", String::truncate($s, 23), "length=23");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se (dnes)\xe2\x80\xa6", String::truncate($s, 24), "length=24");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se (dnes)\xe2\x80\xa6", String::truncate($s, 25), "length=25");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se (dnes)\xe2\x80\xa6", String::truncate($s, 26), "length=26");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se (dnes)\xe2\x80\xa6", String::truncate($s, 27), "length=27");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se (dnes) m\xc3\xa1te?", String::truncate($s, 28), "length=28");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se (dnes) m\xc3\xa1te?", String::truncate($s, 29), "length=29");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se (dnes) m\xc3\xa1te?", String::truncate($s, 30), "length=30");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se (dnes) m\xc3\xa1te?", String::truncate($s, 31), "length=31");
		$this->assertEquals("\xc5\x98ekn\xc4\x9bte, jak se (dnes) m\xc3\xa1te?", String::truncate($s, 32), "length=32");
	}



	/**
	 * indent test.
	 * @return void
	 */
	public function testIndent()
	{
		$this->assertEquals("", String::indent(""));
		$this->assertEquals("\n", String::indent("\n"));
		$this->assertEquals("\tword", String::indent("word"));
		$this->assertEquals("\n\tword", String::indent("\nword"));
		$this->assertEquals("\n\tword", String::indent("\nword"));
		$this->assertEquals("\n\tword\n", String::indent("\nword\n"));
		$this->assertEquals("\r\n\tword\r\n", String::indent("\r\nword\r\n"));
		$this->assertEquals("\r\n\t\tword\r\n", String::indent("\r\nword\r\n", 2));
		$this->assertEquals("\r\n      word\r\n", String::indent("\r\nword\r\n", 2, '   '));
	}



	/**
	 * trim test.
	 * @return void
	 */
	public function testTrim()
	{
		$this->assertEquals("x", String::trim(" \t\n\r\x00\x0B\xC2\xA0x"));
		$this->assertEquals("", String::trim("\xC2x\xA0"));
		$this->assertEquals("a b", String::trim(" a b "));
		$this->assertEquals(" a b ", String::trim(" a b ", ''));
		$this->assertEquals("e", String::trim("\xc5\x98e-", "\xc5\x98-")); // Ře-
	}

}
