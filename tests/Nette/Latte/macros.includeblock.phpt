<?php

/**
 * Test: Nette\Latte\Engine: {includeblock ...}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setTempDirectory(TEMP_DIR);

Assert::matchFile(
	__DIR__ . '/expected/macros.includeblock.phtml',
	$latte->compile(__DIR__ . '/templates/includeblock.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/macros.includeblock.html',
	$latte->renderToString(__DIR__ . '/templates/includeblock.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/macros.includeblock.inc.phtml',
	file_get_contents($latte->getCacheFile(__DIR__ . '/templates/includeblock.inc.latte'))
);
