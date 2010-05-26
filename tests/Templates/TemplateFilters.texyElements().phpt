<?php

/**
 * Test: Nette\Templates\TemplateFilters::texyElements()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\Template,
	Nette\Templates\TemplateFilters;



require __DIR__ . '/../NetteTest/initialize.php';

require __DIR__ . '/Template.inc';



class MockTexy
{
	function process($text, $singleLine = FALSE)
	{
		return '<...>';
	}
}


TemplateFilters::$texy = new MockTexy;

$template = new MockTemplate;
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'texyElements'));
$template->render(NetteTestHelpers::getSection(__FILE__, 'template'));



__halt_compiler() ?>

-----template-----
<texy>**Hello World**</texy>


<texy>
Multi line
----------

example
</texy>


<texy param="value">
Second multi line
-----------------

example
</texy>

------EXPECT------
<...>


<...>


<...>