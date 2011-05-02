<?php

/**
 * Test: Nette\Latte\Engine and macros test.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



$template = new MockTemplate;
$template->registerFilter(new Latte\Engine);

Assert::match(<<<EOD
qwerty

EOD

, $template->render(<<<EOD
{* comment
*}
qwerty

EOD
));



Assert::match(<<<EOD
qwerty

EOD

, $template->render(<<<EOD
{* comment
*}

qwerty

EOD
));



Assert::match(<<<EOD

qwerty

EOD

, $template->render(<<<EOD
{* comment
*}


qwerty

EOD
));



Assert::match(<<<EOD
qwerty

EOD

, $template->render(<<<EOD
{* comment
*}

{contentType text}
qwerty

EOD
));


/* TODO
Assert::match(<<<EOD
qwerty

EOD

, $template->render(<<<EOD
{* comment
*}
{contentType text}
qwerty

EOD
));



Assert::match(<<<EOD
qwerty

EOD

, $template->render(<<<EOD
{* comment
*}
{contentType text/plain}
qwerty

EOD
));
*/
