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



require __DIR__ . '/../NetteTest/initialize.php';

require __DIR__ . '/Template.inc';


$template = new MockTemplate;
$template->registerFilter(new LatteFilter);
try {
	$template->render(NetteTestHelpers::getSection(__FILE__, 'template'));
} catch (Exception $e) {
	dump($e);
}



__halt_compiler() ?>

-----template-----
Block
{/block}

------EXPECT------
Exception InvalidStateException: Filter %ns%LatteFilter::__invoke: Tag {/block } was not expected here on line 2.
