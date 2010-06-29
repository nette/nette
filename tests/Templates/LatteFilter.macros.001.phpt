<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\Template,
	Nette\Templates\LatteFilter;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Template.inc';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
T::purge(TEMP_DIR);
Template::setCacheStorage(new MockCacheStorage(TEMP_DIR));



$template = new Template;
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

$template->render();



__halt_compiler() ?>
