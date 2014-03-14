<?php

/**
 * Test: Nette\Latte\Engine: unclosed macro.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

Assert::exception(function() use ($template) {
	$template->setSource('{if 1}')->compile();
}, 'Nette\Latte\CompileException', 'Missing {/if}');

Assert::exception(function() use ($template) {
	$template->setSource('<p n:foreach=1><span n:if=1>')->compile();
}, 'Nette\Latte\CompileException', 'Missing </span> for macro-attribute n:if');

Assert::exception(function() use ($template) {
	$template->setSource('<p n:foreach=1><span n:if=1></i>')->compile();
}, 'Nette\Latte\CompileException', 'Unexpected </i>, expecting </span> for macro-attribute n:if');

Assert::exception(function() use ($template) {
	$template->setSource('{/if}')->compile();
}, 'Nette\Latte\CompileException', 'Unexpected {/if}');

Assert::exception(function() use ($template) {
	$template->setSource('{if 1}{/foreach}')->compile();
}, 'Nette\Latte\CompileException', 'Unexpected {/foreach}, expecting {/if}');

Assert::exception(function() use ($template) {
	$template->setSource('{if 1}{/if 2}')->compile();
}, 'Nette\Latte\CompileException', 'Unexpected {/if 2}, expecting {/if}');

Assert::exception(function() use ($template) {
	$template->setSource('<span n:if=1 n:foreach=2>{foreach}</span>')->compile();
}, 'Nette\Latte\CompileException', 'Unexpected </span> for macro-attribute n:if and n:foreach, expecting {/foreach}');

Assert::exception(function() use ($template) {
	$template->setSource('<span n:if=1 n:foreach=2>{/foreach}')->compile();
}, 'Nette\Latte\CompileException', 'Unexpected {/foreach}, expecting </span> for macro-attribute n:if and n:foreach');

Assert::exception(function() use ($template) {
	$template->setSource('<span n:if=1 n:foreach=2>{/if}')->compile();
}, 'Nette\Latte\CompileException', 'Unexpected {/if}, expecting </span> for macro-attribute n:if and n:foreach');
