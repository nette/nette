<?php

/**
 * Test: Latte\Engine and JavaScript.
 *
 * @author     David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::exception(function() use ($latte) {
	$latte->compile(<<<'EOD'
<script> '{$var}' </script>
EOD
);
}, 'Latte\CompileException', 'Do not place {$var} inside quotes.');


$latte->compile(<<<'EOD'
<script> '{$var|noescape}' </script>
EOD
);


$latte->compile(<<<'EOD'
<script id='{$var}'> </script>
EOD
);
