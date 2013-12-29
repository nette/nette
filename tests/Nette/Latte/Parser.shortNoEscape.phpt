<?php

/**
 * Test: Nette\Latte\Parser and $shortNoEscape.
 *
 * @author     Miloslav Hůla
 * @package    Nette\Latte
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$template = new Nette\Templating\Template;
$template->registerFilter($latte);

$latte->parser->shortNoEscape = TRUE;
$template->setSource('{="<>"}');
Assert::match('&lt;&gt;', (string) $template);

$template->setSource('{!="<>"}');
Assert::match('<>', (string) $template);

$latte->parser->shortNoEscape = FALSE;
$template->setSource('{="<>"}');
Assert::match('&lt;&gt;', (string) $template);

Assert::error(function() use ($template) {
	$template->setSource('{!="<>"}');
	Assert::match('<>', (string) $template);
}, E_USER_DEPRECATED, 'The noescape shortcut {!...} is deprecated, use {...|noescape} modifier on line 1.');
