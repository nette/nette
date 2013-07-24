<?php

/**
 * Test: Nette\Latte\Engine and invalid UTF-8.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);


Assert::exception(function() use ($template) {
	$template->setSource("\xAA")->compile();
}, 'Nette\InvalidArgumentException', '%a% UTF-8 %a%');
