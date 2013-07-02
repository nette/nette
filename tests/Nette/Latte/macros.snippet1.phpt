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
<div>
	{snippet abc}
	hello
	{/snippet}
</div>
EOD
);

Assert::match(<<<EOD
<div>
<div id="">	hello
</div></div>
EOD
, (string) $template);
