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

/*use Nette\Paginator;*/
/*use Nette\Debug;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @package    Nette
 * @subpackage UnitTests
 */
class NettePaginatorTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Base:0 Page: 3 test.
	 * @return void
	 */
	public function testBase0Page3()
	{
		$paginator = new Paginator;
		$paginator->itemCount = 7;
		$paginator->itemsPerPage = 6;
		$paginator->base = 0;
		$paginator->page = 3;

		$this->assertEquals(1, $paginator->page, "page");
		$this->assertEquals(2, $paginator->pageCount, "pageCount");
		$this->assertEquals(0, $paginator->firstPage, "firstPage");
		$this->assertEquals(1, $paginator->lastPage, "lastPage");
		$this->assertEquals(6, $paginator->offset, "offset");
		$this->assertEquals(0, $paginator->countdownOffset, "countdownOffset");
		$this->assertEquals(1, $paginator->length, "length");
	}



	/**
	 * Base:0 Page: -1 test.
	 * @return void
	 */
	public function testBase0Page1()
	{
		$paginator = new Paginator;
		$paginator->itemCount = 7;
		$paginator->itemsPerPage = 6;
		$paginator->base = 0;
		$paginator->page = -1;

		$this->assertEquals(0, $paginator->page, "page");
		$this->assertEquals(0, $paginator->offset, "offset");
		$this->assertEquals(1, $paginator->countdownOffset, "countdownOffset");
		$this->assertEquals(6, $paginator->length, "length");
	}



	/**
	 * Base:0 Page: -1 PerPage: 7 test.
	 * @return void
	 */
	public function testBase0Page1Perpage7()
	{
		$paginator = new Paginator;
		$paginator->itemCount = 7;
		$paginator->itemsPerPage = 7;
		$paginator->base = 0;
		$paginator->page = -1;

		$this->assertEquals(0, $paginator->page, "page");
		$this->assertEquals(1, $paginator->pageCount, "pageCount");
		$this->assertEquals(0, $paginator->firstPage, "firstPage");
		$this->assertEquals(0, $paginator->lastPage, "lastPage");
		$this->assertEquals(0, $paginator->offset, "offset");
		$this->assertEquals(0, $paginator->countdownOffset, "countdownOffset");
		$this->assertEquals(7, $paginator->length, "length");
	}



	/**
	 * Base:0 Page: -1 Count -1 test.
	 * @return void
	 */
	public function testBase0Page1Count1()
	{
		$paginator = new Paginator;
		$paginator->itemCount = -1;
		$paginator->itemsPerPage = 7;
		$paginator->base = 0;
		$paginator->page = -1;

		$this->assertEquals(0, $paginator->page, "page");
		$this->assertEquals(0, $paginator->pageCount, "pageCount");
		$this->assertEquals(0, $paginator->firstPage, "firstPage");
		$this->assertEquals(0, $paginator->lastPage, "lastPage");
		$this->assertEquals(0, $paginator->offset, "offset");
		$this->assertEquals(0, $paginator->countdownOffset, "countdownOffset");
		$this->assertEquals(0, $paginator->length, "length");
	}



	/**
	 * Base:1 test.
	 * @return void
	 */
	public function testBase1()
	{
		$paginator = new Paginator;
		$paginator->itemCount = 7;
		$paginator->itemsPerPage = 6;
		$paginator->base = 1;
		$paginator->page = 3;

		$this->assertEquals(2, $paginator->page, "page");
		$this->assertEquals(2, $paginator->pageCount, "pageCount");
		$this->assertEquals(1, $paginator->firstPage, "firstPage");
		$this->assertEquals(2, $paginator->lastPage, "lastPage");
		$this->assertEquals(6, $paginator->offset, "offset");
		$this->assertEquals(0, $paginator->countdownOffset, "countdownOffset");
		$this->assertEquals(1, $paginator->length, "length");
	}

}
