<?php

/**
 * Test: Nette\Latte\Engine: {contentType application/xml}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Nette\Utils\Html;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



TestHelpers::purge(TEMP_DIR);
restore_error_handler();



$template = new FileTemplate;
$template->setCacheStorage($cache = new MockCacheStorage);
$template->setFile(__DIR__ . '/templates/xml.latte');
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');

$template->hello = '<i>Hello</i>';
$template->id = ':/item';
$template->people = array('John', 'Mary', 'Paul', ']]>');
$template->comment = 'test -- comment';
$template->netteHttpResponse = Nette\Environment::getHttpResponse();
$template->el = Html::el('div')->title('1/2"');

$result = $template->__toString(TRUE);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.html'), $result);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.phtml'), reset($cache->phtml));
