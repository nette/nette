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

$template->setSource('{define foobar}Hello{/define}');
Assert::match('', (string) $template);


$template->setSource('{define foo-bar}Hello{/define}');
Assert::match('', (string) $template);


$template->foo = 'bar';
$template->setSource('{define $foo}Hello{/define}');
Assert::match('', (string) $template);
