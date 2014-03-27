<?php

/**
 * Test: Nette\Latte\Parser and $shortNoEscape.
 *
 * @author     Miloslav Hůla
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->getParser()->shortNoEscape = TRUE;

Assert::match('&lt;&gt;', $latte->renderToString('{="<>"}'));

Assert::match('<>', $latte->renderToString('{!="<>"}'));

$latte->parser->shortNoEscape = FALSE;
Assert::match('&lt;&gt;', $latte->renderToString('{="<>"}'));

Assert::error(function() use ($latte) {
	$latte->compile('{!="<>"}');
}, E_USER_DEPRECATED, 'The noescape shortcut {!...} is deprecated, use {...|noescape} modifier on line 1.');
