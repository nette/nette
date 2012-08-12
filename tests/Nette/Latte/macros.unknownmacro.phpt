<?php

/**
 * Test: Nette\Latte\Engine: unknown macro.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte;



require __DIR__ . '/../bootstrap.php';



$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

Assert::throws(function() use ($template) {
	$template->setSource('{unknown}')->compile();
}, 'Nette\Latte\CompileException', 'Unknown macro {unknown}');

Assert::throws(function() use ($template) {
	$template->setSource('<style>body {color:blue}</style>')->compile();
}, 'Nette\Latte\CompileException', 'Unknown macro {color:blue} (in JavaScript or CSS, try to put a space after bracket.)');

Assert::throws(function() use ($template) {
	$template->setSource('<script>if (true) {return}</script>')->compile();
}, 'Nette\Latte\CompileException', 'Unknown macro {return} (in JavaScript or CSS, try to put a space after bracket.)');
