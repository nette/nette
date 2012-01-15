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
	{block head}{/block}
</head>
EOD
);

Assert::match(<<<EOD
<head>

</head>
EOD
, (string) $template);
