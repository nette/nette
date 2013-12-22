<?php

/**
 * Test: Nette\Latte\Engine and JavaScript.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);
$template->var = 123;

$template->setSource(<<<'EOD'
<script> '{$var}' </script>
EOD
);

Assert::exception(function() use ($template) {
	$template->compile();
}, 'Nette\Latte\CompileException', 'Do not place {$var} inside quotes.');


$template->setSource(<<<'EOD'
<script> '{$var|noescape}' </script>
EOD
);
$template->compile();


$template->setSource(<<<'EOD'
<script id='{$var}'> </script>
EOD
);
$template->compile();
