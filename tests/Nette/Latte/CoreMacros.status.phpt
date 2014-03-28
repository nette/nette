<?php

/**
 * Test: Nette\Latte\Engine: {status}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Latte\Macros\CoreMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::match('%A%
<?php $netteHttpResponse->setCode(200) ;if (!$netteHttpResponse->isSent()) $netteHttpResponse->setCode(200) ;
', $latte->compile('
{status 200}
{status 200?}
'));
