<?php

/**
 * Test: Nette\Templates\TemplateFilters::texyElements()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/
/*use Nette\Templates\TemplateFilters;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Template.inc';



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