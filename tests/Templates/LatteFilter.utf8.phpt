<?php

/**
 * Test: Nette\Templates\LatteFilter and invalid UTF-8.
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
	$template->render("\xAA");
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\Templates\LatteException', '%a% UTF-8 %a%', $e );
}
