<?php

/**
 * Test: Nette\Latte\Engine: isLinkCurrent()
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Bridges\ApplicationLatte\UIMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
UIMacros::install($latte->getCompiler());

Assert::matchFile(__DIR__ . '/expected/UIMacros.isLinkCurrent.phtml', $latte->compile(
'<a n:href="default" n:class="$presenter->isLinkCurrent() ? current">n:href before n:class</a>

<a n:class="$presenter->isLinkCurrent() ? current" n:href="default">n:href after n:class</a>

<a href="{link default}" n:class="$presenter->isLinkCurrent() ? current">href before n:class</a>

<a n:class="$presenter->isLinkCurrent() ? current" href="{link default}">href after n:class</a>
'));
