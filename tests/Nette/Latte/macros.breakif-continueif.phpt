<?php

/**
 * Test: Nette\Latte\Engine: {breakIf}, {continueIf}.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



$template = new FileTemplate(__DIR__ . '/templates/breakif-continueif.latte');
$template->registerFilter(new Latte\Engine);
$template->people = array('John', 'Mary', 'Paul');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.phtml"), codefix($template->compile()));
Assert::match(file_get_contents("$path.html"), $template->__toString(TRUE));
