<?php

/**
 * Test: Nette\Latte\Engine: {extends ...} test IV.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate;


require __DIR__ . '/../bootstrap.php';


$template = new FileTemplate;
$template->setFile(__DIR__ . '/templates/inheritance.child4.latte');
$template->registerFilter(new Latte\Engine);

Assert::match(<<<EOD
	Content
EOD
, $template->__toString(TRUE));
