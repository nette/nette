<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 * @phpini     short_open_tag=on
 */

use Nette\Templates\Template,
	Nette\Templates\LatteFilter;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Template.inc';


function xml($v) { echo $v; }

$template = new MockTemplate;
$template->registerFilter(new LatteFilter);

Assert::match(<<<EOD
<?xml version="1.0" ?>
12ok

EOD

, $template->render(<<<EOD
<?xml version="1.0" ?>
<?php xml(1) ?>
<? xml(2) ?>
<?php echo 'ok' ?>
EOD
));
