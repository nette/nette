<?php

/**
 * Test: Nette\Latte\Engine: {debugbreak}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

Assert::match('%A%
<?php if (function_exists("debugbreak")) debugbreak(); elseif (function_exists("xdebug_break")) xdebug_break() ;if (!($i==1)); elseif (function_exists("debugbreak")) debugbreak(); elseif (function_exists("xdebug_break")) xdebug_break() ;
', $template->setSource('
{debugbreak}
{debugbreak $i==1}
')->compile());
