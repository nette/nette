<?php

/**
 * Test: Nette\Latte\Engine: isLinkCurrent()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\Template;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



$template = new Template;
$template->registerFilter(new Latte\Engine);

$result = $template->setSource(
'<a n:href="default" n:class="$presenter->isLinkCurrent() ? current">n:href before n:class</a>

<a n:class="$presenter->isLinkCurrent() ? current" n:href="default">n:href after n:class</a>

<a href="{link default}" n:class="$presenter->isLinkCurrent() ? current">href before n:class</a>

<a n:class="$presenter->isLinkCurrent() ? current" href="{link default}">href after n:class</a>
')->compile();

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.phtml"), $result);
