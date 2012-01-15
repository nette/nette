<?php

/**
 * Test: Nette\Latte\Engine and blocks.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

$template->setSource(<<<EOD
<head>
	<script src="nette.js"></script>
	{include #meta}
</head>

{block meta}
	<link rel="alternate">
{/block}
EOD
);

Assert::match(<<<EOD
<head>
	<script src="nette.js"></script>
	<link rel="alternate" />
</head>

	<link rel="alternate" />
EOD
, (string) $template);
