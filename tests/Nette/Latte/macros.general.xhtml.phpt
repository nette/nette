<?php

/**
 * Test: Nette\Latte\Engine: general XHTML test.
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
$latte->compiler->defaultContentType = Latte\Compiler::CONTENT_XHTML;
$template = new FileTemplate(__DIR__ . '/templates/general.latte');
$template->registerFilter($latte);
$template->registerHelper('translate', 'strrev');
$template->registerHelper('join', 'implode');
$template->registerHelperLoader('Nette\Templating\Helpers::loader');

$template->hello = '<i>Hello</i>';
$template->xss = 'some&<>"\'/chars';
$template->people = array('John', 'Mary', 'Paul', ']]>');
$template->menu = array('about', array('product1', 'product2'), 'contact');
$template->el = Html::el('div')->title('1/2"');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", codefix($template->compile()));
Assert::matchFile("$path.html", $template->__toString(TRUE));
