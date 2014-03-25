<?php

/**
 * Test: Nette\Latte\Engine: whitespace test I.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::match(<<<EOD
qwerty

EOD

, $latte->renderToString(<<<EOD
{contentType text}
qwerty

EOD
));


Assert::match(<<<EOD

asdfgh
EOD

, $latte->renderToString(<<<EOD

{contentType text}
asdfgh
EOD
));
