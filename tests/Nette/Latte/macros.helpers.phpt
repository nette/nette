<?php

/**
 * Test: Nette\Latte\Engine: helpers test.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


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


$template = new FileTemplate(__DIR__ . '/templates/helpers.latte');
$template->registerFilter(new Latte\Engine);
$template->registerHelper('nl2br', 'nl2br');
$template->registerHelper('h1', array(new MyHelper, 'invoke'));
$template->registerHelper('h2', 'strtoupper');
$template->registerHelper('translate', 'strrev');
$template->registerHelper('types', 'types');
$template->registerHelperLoader('Nette\Latte\Runtime\Filters::loader');

$template->hello = 'Hello World';
$template->date = strtotime('2008-01-02');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", $template->compile());
Assert::matchFile("$path.html", $template->__toString(TRUE));
