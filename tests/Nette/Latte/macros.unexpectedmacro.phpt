<?php

/**
 * Test: Nette\Latte\Engine: unexpected macro.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);
Assert::exception(function() use ($template) {
	$template->setSource('Block{/block}')->compile();
}, 'Nette\Latte\CompileException', 'Unexpected {/block}');
