<?php

/**
 * Test: Latte\Engine: {use ...}
 *
 * @author     David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MyMacros extends Latte\Macros\MacroSet
{
	public function __construct($compiler)
	{
		parent::__construct($compiler);
		$this->addMacro('my', 'echo "ok"');
	}
}


$latte = new Latte\Engine;

Assert::matchFile(
	__DIR__ . '/expected/macros.use.phtml',
	$latte->compile(__DIR__ . '/templates/use.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/macros.use.html',
	$latte->renderToString(__DIR__ . '/templates/use.latte')
);
