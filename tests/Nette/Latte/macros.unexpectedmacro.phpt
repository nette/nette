<?php

/**
 * Test: Nette\Latte\Engine: unexpected macro.
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
Assert::throws(function() use ($template) {
	$template->setSource('Block{/block}')->compile();
}, 'Nette\Latte\CompileException', 'Unexpected macro {/block}');
