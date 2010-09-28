<?php

/**
 * Test: Nette\Templates\TemplateFilters::removePhp()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



$template = new MockTemplate;
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'removePhp'));

Assert::match(<<<EOD
Hello World!

<?php doEvil(); ?>
EOD

, $template->render(<<<EOD
Hello<?php echo '?>hacked!'; ?> World!

<<?php ?>?php doEvil(); ?>

EOD
));
