<?php

/**
 * Test: Nette\Latte\Engine: {contentType application/xml}
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


restore_error_handler();


$template = new FileTemplate(__DIR__ . '/templates/xml.latte');
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Templating\Helpers::loader');

$template->hello = '<i>Hello</i>';
$template->id = ':/item';
$template->people = array('John', 'Mary', 'Paul', ']]>');
$template->comment = 'test -- comment';
$template->netteHttpResponse = new Nette\Http\Response;
$template->el = Html::el('div')->title('1/2"');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", codefix($template->compile()));
Assert::matchFile("$path.html", $template->__toString(TRUE));
