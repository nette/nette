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