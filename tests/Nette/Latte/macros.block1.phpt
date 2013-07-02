<?php

/**
 * Test: Nette\Latte\Engine and blocks.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

$template->setSource(<<<EOD
	{block main}
	<div id="main">
		{block sidebar}side{/block}
	</div> <!-- /main -->
	{/block}

	{include #sidebar}

{include #main}
EOD
);

Assert::match(<<<EOD
	<div id="main">
		side
	</div> <!-- /main -->

side
	<div id="main">
		side
	</div> <!-- /main -->
EOD
, (string) $template);
