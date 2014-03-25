<?php

/**
 * Test: Nette\Latte\Engine: general HTML test.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$latte = new Latte\Engine;
$latte->setContentType($latte::CONTENT_HTML);
$latte->addFilterLoader('Nette\Latte\Runtime\Filters::loader');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	codefix($latte->compile(__DIR__ . '/templates/recursive.latte'))
);
