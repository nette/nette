<?php

/**
 * Test: Nette\Latte\Engine: dynamic snippets test.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Utils\Html,
	Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



$template = new FileTemplate(__DIR__ . '/templates/dynamicsnippets.latte');
$template->registerFilter(new Latte\Engine);

$result = $template->compile();
$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.phtml"), codefix($result));
