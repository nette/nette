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
try {
	$template->setSource('Block{/block}')->compile();
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\Latte\ParseException', 'Unexpected macro {/block}', $e );
}
