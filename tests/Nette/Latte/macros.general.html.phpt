<?php

/**
 * Test: Nette\Latte\Engine: general HTML test.
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



TestHelpers::purge(TEMP_DIR);


Html::$xhtml = FALSE;
$template = new FileTemplate;
$template->setCacheStorage($cache = new MockCacheStorage);
$template->setFile(__DIR__ . '/templates/general.latte');
$template->registerFilter(new Latte\Engine);
$template->registerHelper('translate', 'strrev');
$template->registerHelper('join', 'implode');
$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');

$template->hello = '<i>Hello</i>';
$template->xss = 'some&<>"\'/chars';
$template->people = array('John', 'Mary', 'Paul', ']]>');
$template->menu = array('about', array('product1', 'product2'), 'contact');
$template->comment = 'test -- comment';
$template->el = Html::el('div')->title('1/2"');

$result = $template->__toString(TRUE);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.html'), $result);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.phtml'), $cache->phtml['general.latte']);
