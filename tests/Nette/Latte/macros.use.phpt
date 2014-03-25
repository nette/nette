<?php

/**
 * Test: Nette\Latte\Engine: {use ...}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


class MyMacros extends Latte\Macros\MacroSet
{
	public function __construct($compiler)
	{
		parent::__construct($compiler);
		$this->addMacro('my', 'echo "ok"');
	}
}


$latte = new Latte\Engine;

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	codefix($latte->compile(__DIR__ . '/templates/use.latte'))
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(__DIR__ . '/templates/use.latte')
);
