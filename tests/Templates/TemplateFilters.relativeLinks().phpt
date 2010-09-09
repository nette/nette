<?php

/**
 * Test: Nette\Templates\TemplateFilters::relativeLinks()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\Template;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Template.inc';



$template = new MockTemplate;
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'relativeLinks'));

$template->baseUri = 'http://example.com/~my/';

Assert::match(<<<EOD
<a href="http://example.com/~my/relative">link</a>

<a href="http://example.com/~my/relative#fragment">link</a>

<a href="#fragment">link</a>

<a href="http://url">link</a>

<a href="mailto:john@example.com">link</a>

<a href="/absolute-path">link</a>

<a href="//absolute">link</a>
EOD

, $template->render(<<<EOD
<a href="relative">link</a>

<a href="relative#fragment">link</a>

<a href="#fragment">link</a>

<a href="http://url">link</a>

<a href="mailto:john@example.com">link</a>

<a href="/absolute-path">link</a>

<a href="//absolute">link</a>
EOD
));
