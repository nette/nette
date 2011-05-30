<?php

/**
 * Test: Nette\Latte\Parser and invalid UTF-8.
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
	$template->render("\xAA");
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\Latte\ParseException', '%a% UTF-8 %a%', $e );
}
