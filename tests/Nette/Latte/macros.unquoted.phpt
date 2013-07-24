<?php

/**
 * Test: Nette\Latte\Engine: unquoted attributes.
 *
 * @author     Jakub Vrana
 * @package    Nette\Latte
 */

use Nette\Latte,
	Nette\Templating\FileTemplate;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$template = new FileTemplate(__DIR__ . '/templates/unquoted.latte');
$template->registerFilter(new Latte\Engine);
$template->x = '\' & "';

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", codefix($template->compile()));
Assert::matchFile("$path.html", $template->__toString(TRUE));
