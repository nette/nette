<?php

/**
 * Test: Nette\Latte\Engine: {? ... }
 */

use Nette\Latte;
use Nette\Templating\FileTemplate;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$template = new FileTemplate(__DIR__ . '/templates/php.latte');
$template->registerFilter(new Latte\Engine);

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.phtml"), $template->compile());
