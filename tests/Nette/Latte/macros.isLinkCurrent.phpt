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



TestHelpers::purge(TEMP_DIR);



$template = new Template;
$template->setCacheStorage(new MockCacheStorage(TEMP_DIR));
$template->registerFilter(new Latte\Engine);

$result = $template->compile(
'<a n:href="default" n:class="$presenter->isLinkCurrent() ? current">n:href before n:class</a>

<a n:class="$presenter->isLinkCurrent() ? current" n:href="default">n:href after n:class</a>

<a href="{link default}" n:class="$presenter->isLinkCurrent() ? current">href before n:class</a>

<a n:class="$presenter->isLinkCurrent() ? current" href="{link default}">href after n:class</a>
');

Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.phtml'), $result);
