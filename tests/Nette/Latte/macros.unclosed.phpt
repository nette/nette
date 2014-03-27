<?php

/**
 * Test: Nette\Latte\Engine: unclosed macro.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::exception(function() use ($latte) {
	$latte->compile('{if 1}');
}, 'Nette\Latte\CompileException', 'Missing {/if}');

Assert::exception(function() use ($latte) {
	$latte->compile('<p n:foreach=1><span n:if=1>');
}, 'Nette\Latte\CompileException', 'Missing </span> for macro-attribute n:if');

Assert::exception(function() use ($latte) {
	$latte->compile('<p n:foreach=1><span n:if=1></i>');
}, 'Nette\Latte\CompileException', 'Unexpected </i>, expecting </span> for macro-attribute n:if');

Assert::exception(function() use ($latte) {
	$latte->compile('{/if}');
}, 'Nette\Latte\CompileException', 'Unexpected {/if}');

Assert::exception(function() use ($latte) {
	$latte->compile('{if 1}{/foreach}');
}, 'Nette\Latte\CompileException', 'Unexpected {/foreach}, expecting {/if}');

Assert::exception(function() use ($latte) {
	$latte->compile('{if 1}{/if 2}');
}, 'Nette\Latte\CompileException', 'Unexpected {/if 2}, expecting {/if}');

Assert::exception(function() use ($latte) {
	$latte->compile('<span n:if=1 n:foreach=2>{foreach}</span>');
}, 'Nette\Latte\CompileException', 'Unexpected </span> for macro-attribute n:if and n:foreach, expecting {/foreach}');

Assert::exception(function() use ($latte) {
	$latte->compile('<span n:if=1 n:foreach=2>{/foreach}');
}, 'Nette\Latte\CompileException', 'Unexpected {/foreach}, expecting </span> for macro-attribute n:if and n:foreach');

Assert::exception(function() use ($latte) {
	$latte->compile('<span n:if=1 n:foreach=2>{/if}');
}, 'Nette\Latte\CompileException', 'Unexpected {/if}, expecting </span> for macro-attribute n:if and n:foreach');
