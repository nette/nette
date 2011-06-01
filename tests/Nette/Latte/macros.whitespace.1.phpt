<?php

/**
 * Test: Nette\Latte\Engine: whitespace test I.
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

Assert::match(<<<EOD
qwerty

EOD

, (string) $template->setSource(<<<EOD
{contentType text}
qwerty

EOD
));



Assert::match(<<<EOD

asdfgh
EOD

, (string) $template->setSource(<<<EOD

{contentType text}
asdfgh
EOD
));
