<?php

/**
 * Test: CompileExceptions have correct line number in mixed php/latte template
 *
 * @author     Jan Dolecek, David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



$template = new FileTemplate(__DIR__ . '/templates/mixedCode.latte');
$template->registerFilter(new Latte\Engine);

try {
	$template->compile();
} catch(\Nette\Latte\CompileException $e) {
	Assert::same(4, $e->sourceLine);
	Assert::same("Unknown macro {notDefined}", $e->getMessage());
}
