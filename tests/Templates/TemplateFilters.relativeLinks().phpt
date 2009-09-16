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



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);



$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/relative-links.phtml');
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'relativeLinks'));

$template->baseUri = 'http://example.com/~my/';

$template->render();



__halt_compiler();

------EXPECT------
<a href="http://example.com/~my/relative">link</a>

<a href="http://example.com/~my/relative#fragment">link</a>

<a href="#fragment">link</a>

<a href="http://url">link</a>

<a href="mailto:john@example.com">link</a>

<a href="/absolute-path">link</a>

<a href="//absolute">link</a>
