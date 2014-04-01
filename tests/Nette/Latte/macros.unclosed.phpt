<?php

/**
 * Test: Latte\Engine: unclosed macro.
 *
 * @author     David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::exception(function() use ($latte) {
	$latte->compile('{if 1}');
}, 'Latte\CompileException', 'Missing {/if}');

Assert::exception(function() use ($latte) {
	$latte->compile('<p n:foreach=1><span n:if=1>');
}, 'Latte\CompileException', 'Missing </span> for macro-attribute n:if');

Assert::exception(function() use ($latte) {
	$latte->compile('<p n:foreach=1><span n:if=1></i>');
}, 'Latte\CompileException', 'Unexpected </i>, expecting </span> for macro-attribute n:if');

Assert::exception(function() use ($latte) {
	$latte->compile('{/if}');
}, 'Latte\CompileException', 'Unexpected {/if}');

Assert::exception(function() use ($latte) {
	$latte->compile('{if 1}{/foreach}');
}, 'Latte\CompileException', 'Unexpected {/foreach}, expecting {/if}');

Assert::exception(function() use ($latte) {
	$latte->compile('{if 1}{/if 2}');
}, 'Latte\CompileException', 'Unexpected {/if 2}, expecting {/if}');

Assert::exception(function() use ($latte) {
	$latte->compile('<span n:if=1 n:foreach=2>{foreach}</span>');
}, 'Latte\CompileException', 'Unexpected </span> for macro-attribute n:if and n:foreach, expecting {/foreach}');

Assert::exception(function() use ($latte) {
	$latte->compile('<span n:if=1 n:foreach=2>{/foreach}');
}, 'Latte\CompileException', 'Unexpected {/foreach}, expecting </span> for macro-attribute n:if and n:foreach');

Assert::exception(function() use ($latte) {
	$latte->compile('<span n:if=1 n:foreach=2>{/if}');
}, 'Latte\CompileException', 'Unexpected {/if}, expecting </span> for macro-attribute n:if and n:foreach');
