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


$template = new MockTemplate;
$template->registerFilter(new LatteFilter);
try {
	$template->render('Block{/block}');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidStateException', 'Filter Nette\Templates\LatteFilter::__invoke: Tag {/block } was not expected here on line %a%.', $e );
}
