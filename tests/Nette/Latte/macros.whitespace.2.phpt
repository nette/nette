<?php

/**
 * Test: Nette\Latte\Engine: whitespace test II.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

Assert::match(<<<EOD
qwerty

EOD

, (string) $template->setSource(<<<EOD
{* comment
*}
qwerty

EOD
));


Assert::match(<<<EOD
qwerty

EOD

, (string) $template->setSource(<<<EOD
{* comment
*}

qwerty

EOD
));


Assert::match(<<<EOD

qwerty

EOD

, (string) $template->setSource(<<<EOD
{* comment
*}


qwerty

EOD
));


Assert::match(<<<EOD
qwerty

EOD

, (string) $template->setSource(<<<EOD
{* comment
*}

{contentType text}
qwerty

EOD
));


Assert::match(<<<EOD
qwerty

EOD

, (string) $template->setSource(<<<EOD
{* comment
*}
{contentType text}
qwerty

EOD
));


Assert::match(<<<EOD
line 1
line 2
EOD

, (string) $template->setSource(<<<EOD
line 1 {* comment *}
line 2
EOD
));


Assert::match(<<<EOD
word 1  word 2
EOD

, (string) $template->setSource(<<<EOD
word 1 {* comment *} word 2
EOD
));
