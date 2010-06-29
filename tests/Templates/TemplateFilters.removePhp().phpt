<?php

/**
 * Test: Nette\Templates\TemplateFilters::removePhp()
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
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'removePhp'));
$template->render(T::getSection(__FILE__, 'template'));



__halt_compiler() ?>

-----template-----
Hello<?php echo '?>hacked!'; ?> World!

<<?php ?>?php doEvil(); ?>

------EXPECT------
Hello World!

<?php doEvil(); ?>