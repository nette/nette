<?php

/**
 * Test: Nette\Latte\Engine and Nette\Utils\Html::$xhtml.
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



// purge temporary directory
TestHelpers::purge(TEMP_DIR);


Html::$xhtml = FALSE;
$template = new FileTemplate;
$template->setCacheStorage(new MockCacheStorage(TEMP_DIR));
$template->setFile(__DIR__ . '/templates/common.latte');
$template->registerFilter(new Latte\Engine);
$template->registerHelper('translate', 'strrev');
$template->registerHelper('join', 'implode');
$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');

$template->hello = '<i>Hello</i>';
$template->id = ':/item';
$template->people = array('John', 'Mary', 'Paul', ']]>');
$template->menu = array('about', array('product1', 'product2'), 'contact');
$template->comment = 'test -- comment';
$template->el = Html::el('div')->title('1/2"');

Assert::match(file_get_contents(__DIR__ . '/test.018.expect'), $template->__toString(TRUE));
