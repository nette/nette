<?php

/**
 * Test: Nette\Latte\Engine: {status}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

Assert::match('%A%
<?php $netteHttpResponse->setCode(200) ;if (!$netteHttpResponse->isSent()) $netteHttpResponse->setCode(200) ;
', $template->setSource('
{status 200}
{status 200?}
')->compile());
