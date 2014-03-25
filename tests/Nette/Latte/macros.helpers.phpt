<?php

/**
 * Test: Nette\Latte\Engine: helpers test.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


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


$latte = new Latte\Engine;
$latte->addFilter('nl2br', 'nl2br');
$latte->addFilter('h1', array(new MyHelper, 'invoke'));
$latte->addFilter('h2', 'strtoupper');
$latte->addFilter('translate', 'strrev');
$latte->addFilter('types', 'types');
$latte->addFilterLoader('Nette\Latte\Runtime\Filters::loader');

$params['hello'] = 'Hello World';
$params['date'] = strtotime('2008-01-02');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	codefix($latte->compile(__DIR__ . '/templates/helpers.latte'))
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/helpers.latte',
		$params
	)
);
