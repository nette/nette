<?php

/**
 * Test: Nette\Latte\Engine and blocks.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte;


require __DIR__ . '/../bootstrap.php';


class MockControl
{
	function __call($name, $args)
	{
	}
}


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);
$template->_control = new MockControl;

$template->setSource(<<<EOD
<p>{snippet abc}hello{/snippet} world</p>
EOD
);

Assert::match(<<<EOD
<p><div id="">hello</div> world</p>
EOD
, (string) $template);
