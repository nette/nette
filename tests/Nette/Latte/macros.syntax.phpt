<?php

/**
 * Test: Nette\Latte\Engine: {syntax ...}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$latte = new Latte\Engine;
$latte->addFilterLoader('Nette\Latte\Runtime\Filters::loader');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	codefix($latte->compile(__DIR__ . '/templates/syntax.latte'))
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/syntax.latte',
		array('people' => array('John', 'Mary', 'Paul'))
	)
);
