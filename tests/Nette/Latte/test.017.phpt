<?php

/**
 * Test: Nette\Latte\Engine and macros test.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @phpini     short_open_tag=on
 */

use Nette\Latte;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


function xml($v) { echo $v; }

$template = new MockTemplate;
$template->registerFilter(new Latte\Engine);

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
