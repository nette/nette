<?php

/**
 * Test: Nette\Templating\Template helpers override.
 *
 * @author     Filip Procházka
 * @package    Nette\Templating
 */

use Nette\Templating\Template;


require __DIR__ . '/../bootstrap.php';


class MyHelpers
{

	public static function loader($helper)
	{
		if (method_exists(__CLASS__, $helper)) {
			return array(__CLASS__, $helper);
		}
	}


	public static function date($s)
	{
		Notes::add(__METHOD__);
		return Nette\DateTime::from($s)->format('Y-m-d');
	}

}


$template = new Template();
$template->registerHelperLoader('Nette\Templating\Helpers::loader');
$template->registerHelperLoader('MyHelpers::loader');


Assert::same( '1978-01-23', $template->date(new \Datetime('Mon, 23 Jan 1978 10:00:00 GMT'), 'd.m.Y') );
Assert::same( array('MyHelpers::date'), Notes::fetch() );
