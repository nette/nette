<?php

/**
 * Test: Nette\Latte\Engine: {block} autoclosing
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

Assert::match(<<<EOD
Block

EOD

, (string) $template->setSource(<<<EOD
{block}
Block

EOD
));
