<?php

/**
 * Test: Nette\Latte\Engine: {contentType}
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

Assert::match('%A%
<?php $netteHttpResponse->setCode(200) ;if (!$netteHttpResponse->isSent()) $netteHttpResponse->setCode(200) ;
', $template->setSource('
{status 200}
{status 200?}
')->compile());
