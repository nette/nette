<?php

/**
 * Test: Nette\Latte\Engine and n:ifcontent.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::match(<<<EOD
<div>Content</div>
EOD
, $latte->renderToString(<<<EOD
<div n:ifcontent>Content</div>
EOD
));


Assert::match(<<<EOD
EOD
, $latte->renderToString(<<<EOD
<div n:ifcontent></div>
EOD
));


Assert::match(<<<EOD
<div>0</div>
EOD
, $latte->renderToString(<<<'EOD'
<div n:ifcontent>{$content}</div>
EOD
, array('content' => '0')));


Assert::match(<<<EOD
EOD
, $latte->renderToString(<<<'EOD'
<div n:ifcontent>{$empty}</div>
EOD
, array('empty' => '')));


Assert::match(<<<EOD
EOD
, $latte->renderToString(<<<EOD
<div n:ifcontent>

</div>
EOD
));


Assert::match(<<<EOD
EOD
, $latte->renderToString(<<<'EOD'
<div n:ifcontent>
	{$empty}
</div>
EOD
, array('empty' => '')));


Assert::exception(function() use ($latte) {
	$latte->compile('{ifcontent}');
}, 'Nette\Latte\CompileException', 'Unknown macro {ifcontent}, use n:ifcontent attribute.');


Assert::exception(function() use ($latte) {
	$latte->compile('<div n:inner-ifcontent>');
}, 'Nette\Latte\CompileException', 'Unknown attribute n:inner-ifcontent, use n:ifcontent attribute.');
