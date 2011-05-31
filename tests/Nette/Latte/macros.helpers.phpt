<?php

/**
 * Test: Nette\Latte\Engine: helpers test.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



TestHelpers::purge(TEMP_DIR);



class MyHelper
{
	protected $count = 0;

	public function invoke($s)
	{
		$this->count++;
		return strtolower($s) . " ($this->count times)";
	}

}

function types()
{
	foreach (func_get_args() as $arg) $res[] = gettype($arg);
	return implode(', ', $res);
}



$template = new FileTemplate;
$template->setCacheStorage($cache = new MockCacheStorage);
$template->setFile(__DIR__ . '/templates/helpers.latte');
$template->registerFilter(new Latte\Engine);
$template->registerHelper('nl2br', 'nl2br');
$template->registerHelper('h1', array(new MyHelper, 'invoke'));
$template->registerHelper('h2', 'strtoupper');
$template->registerHelper('translate', 'strrev');
$template->registerHelper('types', 'types');
$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');

$template->hello = 'Hello World';
$template->date = strtotime('2008-01-02');

$result = $template->__toString(TRUE);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.html'), $result);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.phtml'), reset($cache->phtml));
