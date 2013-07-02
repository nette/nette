<?php

/**
 * Test: Nette\Latte\Parser and $shortNoEscape.
 *
 * @author     Miloslav Hůla
 * @package    Nette\Latte
 */

use Nette\Latte;


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

Assert::exception(function() use ($template) {
	$template->setSource('{!="<>"}');
	Assert::match('<>', $template->render());
}, 'Nette\Latte\CompileException', 'The noescape shortcut (exclamation mark) is not enabled, use the noescape modifier on line 1.');
