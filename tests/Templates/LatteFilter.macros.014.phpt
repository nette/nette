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
$template->render(T::getSection(__FILE__, 'template'));



__halt_compiler() ?>

-----template-----
{block}
Block

------EXPECT------
Block
