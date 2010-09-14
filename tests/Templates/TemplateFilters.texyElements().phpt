<?php

/**
 * Test: Nette\Templates\TemplateFilters::texyElements()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\Template,
	Nette\Templates\TemplateFilters;



require __DIR__ . '/../initialize.php';

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

Assert::match(<<<EOD
<...>


<...>


<...>
EOD

, $template->render(<<<EOD
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
EOD
));
