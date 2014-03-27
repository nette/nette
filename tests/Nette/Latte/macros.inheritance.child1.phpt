<?php

/**
 * Test: Nette\Latte\Engine: {extends ...} test I.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setTempDirectory(TEMP_DIR);

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.child.phtml",
	$latte->compile(__DIR__ . '/templates/inheritance.child1.latte')
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/inheritance.child1.latte',
		array('people' => array('John', 'Mary', 'Paul'))
	)
);
Assert::matchFile("$path.parent.phtml", file_get_contents($latte->getCacheFile(__DIR__ . '/templates/inheritance.parent.latte')));
