<?php

/**
 * Test: Nette\Latte\Engine: comments HTML test.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Nette\Utils\Html;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



$latte = new Latte\Engine;
$latte->compiler->defaultContentType = Latte\Compiler::CONTENT_HTML;
$template = new FileTemplate(__DIR__ . '/templates/comments.latte');
$template->registerFilter(new Latte\Engine);
$template->gt = '>';
$template->dash = '-';

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.html"), $template->__toString(TRUE));
