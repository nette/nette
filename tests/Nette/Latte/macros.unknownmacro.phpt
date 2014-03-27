<?php

/**
 * Test: Nette\Latte\Engine: unknown macro.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::exception(function() use ($latte) {
	$latte->compile('{unknown}');
}, 'Nette\Latte\CompileException', 'Unknown macro {unknown}');

Assert::exception(function() use ($latte) {
	$latte->compile('<style>body {color:blue}</style>');
}, 'Nette\Latte\CompileException', 'Unknown macro {color:blue} (in JavaScript or CSS, try to put a space after bracket.)');

Assert::exception(function() use ($latte) {
	$latte->compile('<script>if (true) {return}</script>');
}, 'Nette\Latte\CompileException', 'Unknown macro {return} (in JavaScript or CSS, try to put a space after bracket.)');
