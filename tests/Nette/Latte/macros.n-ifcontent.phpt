<?php

/**
 * Test: Nette\Latte\Engine and n:ifcontent.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);
$template->content = '0';
$template->empty = '';

$template->setSource(<<<EOD
<div n:ifcontent>Content</div>
EOD
);

Assert::match(<<<EOD
<div>Content</div>
EOD
, (string) $template);


$template->setSource(<<<EOD
<div n:ifcontent></div>
EOD
);

Assert::match(<<<EOD
EOD
, (string) $template);


$template->setSource(<<<'EOD'
<div n:ifcontent>{$content}</div>
EOD
);

Assert::match(<<<EOD
<div>0</div>
EOD
, (string) $template);


$template->setSource(<<<'EOD'
<div n:ifcontent>{$empty}</div>
EOD
);

Assert::match(<<<EOD
EOD
, (string) $template);


Assert::exception(function() use ($template) {
	$template->setSource('{ifcontent}')->compile();
}, 'Nette\Latte\CompileException', 'Unknown macro {ifcontent}, use n:ifcontent attribute.');


Assert::exception(function() use ($template) {
	$template->setSource('<div n:inner-ifcontent>')->compile();
}, 'Nette\Latte\CompileException', 'Unknown attribute n:inner-ifcontent, use n:ifcontent attribute.');
