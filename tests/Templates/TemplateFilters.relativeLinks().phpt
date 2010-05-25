<?php

/**
 * Test: Nette\Templates\TemplateFilters::relativeLinks()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Template.inc';



$template = new MockTemplate;
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'relativeLinks'));

$template->baseUri = 'http://example.com/~my/';

$template->render(NetteTestHelpers::getSection(__FILE__, 'template'));



__halt_compiler() ?>

-----template-----
<a href="relative">link</a>

<a href="relative#fragment">link</a>

<a href="#fragment">link</a>

<a href="http://url">link</a>

<a href="mailto:john@example.com">link</a>

<a href="/absolute-path">link</a>

<a href="//absolute">link</a>

------EXPECT------
<a href="http://example.com/~my/relative">link</a>

<a href="http://example.com/~my/relative#fragment">link</a>

<a href="#fragment">link</a>

<a href="http://url">link</a>

<a href="mailto:john@example.com">link</a>

<a href="/absolute-path">link</a>

<a href="//absolute">link</a>
