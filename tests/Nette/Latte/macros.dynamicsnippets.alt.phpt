<?php

/**
 * Test: Nette\Latte\Engine: dynamic snippets test.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @keepTrailingSpaces
 */

use Nette\Latte;
use Nette\Utils\Html;
use Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



$template = new FileTemplate(__DIR__ . '/templates/dynamicsnippets.alt.latte');
$template->registerFilter(new Latte\Engine);

$result = $template->compile();
$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.phtml"), codefix($result));
