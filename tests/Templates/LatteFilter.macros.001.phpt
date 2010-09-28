<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Templates\FileTemplate,
	Nette\Templates\LatteFilter;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
TestHelpers::purge(TEMP_DIR);
FileTemplate::setCacheStorage(new MockCacheStorage(TEMP_DIR));



$template = new FileTemplate;
$template->setFile(__DIR__ . '/templates/latte.phtml');
$template->registerFilter(new LatteFilter);
$template->registerHelper('translate', 'strrev');
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');

$template->hello = '<i>Hello</i>';
$template->id = ':/item';
$template->people = array('John', 'Mary', 'Paul', ']]>');
$template->menu = array('about', array('product1', 'product2'), 'contact');
$template->comment = 'test -- comment';
$template->el = Nette\Web\Html::el('div')->title('1/2"');

Assert::match(file_get_contents(__DIR__ . '/LatteFilter.macros.001.expect'), (string) $template);
