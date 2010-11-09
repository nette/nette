<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteFilter;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$template = new MockTemplate;
$template->registerFilter(new LatteFilter);
try {
	$template->render('Block{/block}');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\Templates\LatteException', 'Tag {/block } was not expected here.', $e );
}
