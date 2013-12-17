<?php

/**
 * Test: Nette\Latte\Engine: {? ... }
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte,
	Nette\Templating\FileTemplate;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$template = new FileTemplate(__DIR__ . '/templates/php.latte');
$template->registerFilter(new Latte\Engine);

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.phtml"), codefix($template->compile()));
