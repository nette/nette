<?php

/**
 * Test: Nette\Latte\Engine: {debugbreak}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::match('%A%
<?php if (function_exists("debugbreak")) debugbreak(); elseif (function_exists("xdebug_break")) xdebug_break() ;if (!($i==1)); elseif (function_exists("debugbreak")) debugbreak(); elseif (function_exists("xdebug_break")) xdebug_break() ;
', $latte->compile('
{debugbreak}
{debugbreak $i==1}
'));
