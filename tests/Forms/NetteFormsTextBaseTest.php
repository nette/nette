<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * @category   Nette
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

/*use Nette\Debug;*/
/*use Nette\Forms\TextBase;*/
/*use Nette\Forms\TextInput;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @package    Nette\Forms
 * @subpackage UnitTests
 */
class NetteFormsTextBaseTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Email validation test.
	 * @return void
	 */
	public function testEmailValidation()
	{
		$control = new TextInput();
		$control->value = '';
		$this->assertEquals(FALSE, TextBase::validateEmail($control), $control->value);

		$control->value = '@.';
		$this->assertEquals(FALSE, TextBase::validateEmail($control), $control->value);

		$control->value = 'name@a-b-c.cz';
		$this->assertEquals(TRUE, TextBase::validateEmail($control), $control->value);

		$control->value = "name@\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd.cz"; // name@žluouèký.cz
		$this->assertEquals(TRUE, TextBase::validateEmail($control), $control->value);

		$control->value = "\xc5\xbename@\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd.cz"; // žname@žluouèký.cz
		$this->assertEquals(FALSE, TextBase::validateEmail($control), $control->value);
	}

}
