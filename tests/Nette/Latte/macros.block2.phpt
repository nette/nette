<?php

/**
 * Test: Nette\Latte\Engine and blocks.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

$template->setSource(<<<EOD
<head>
	{block head}{/block}
</head>
EOD
);

Assert::match(<<<EOD
<head>

</head>
EOD
, (string) $template);
