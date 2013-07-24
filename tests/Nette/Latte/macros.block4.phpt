<?php

/**
 * Test: Nette\Latte\Engine and blocks.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

$template->setSource(<<<EOD
{#block}<div>Content</div>{/#}
EOD
);

Assert::match(<<<EOD
<div>Content</div>
EOD
, (string) $template);


$template->setSource(<<<EOD
<p n:#="abc">hello</p>
EOD
);

Assert::match(<<<EOD
<p>hello</p>
EOD
, (string) $template);


$template->setSource(<<<EOD
{#block}Content
EOD
);

Assert::match(<<<EOD
Content
EOD
, (string) $template);
