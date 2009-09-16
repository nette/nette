<?php

/**
 * Test: Nette\Templates\TemplateFilters::removePhp()
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
//$template->setCacheStorage(new /*Nette\Caching\*/DummyStorage);
$template->setFile(dirname(__FILE__) . '/templates/remove-php.phtml');
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'removePhp'));
$template->render();



__halt_compiler();

------EXPECT------
Hello World!

<?php doEvil(); ?>