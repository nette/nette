<?php

/**
 * Test: Latte\Helpers::optimizePhp()
 *
 * @author     David Grudl
 */

use Latte\Helpers,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$input = file_get_contents(__DIR__ . '/templates/optimize.phtml');
$expected = file_get_contents(__DIR__ . '/expected/Helpers.optimizePhp().phtml');
Assert::match($expected, Helpers::optimizePhp($input));

Assert::match('<<?php ?>?xml version="1.0" ?>', Helpers::optimizePhp('<?xml version="1.0" ?>'));
Assert::match('<<?php ?>?xml version="1.0" ?>', Helpers::optimizePhp('<?php ?><?xml version="1.0" ?>'));
Assert::match('<?php echo "<?xml" ;', Helpers::optimizePhp('<?php echo "<?xml" ?>'));
