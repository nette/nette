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
<p n:inner-snippet="abc">hello</p>
EOD
);

Assert::match(<<<EOD
<p id="">hello</p>
EOD
, (string) $template);

$template->setSource(<<<EOD
<p n:snippet="abc">hello</p>
EOD
);

Assert::match(<<<EOD
<p id="">hello</p>
EOD
, (string) $template);
