<?php

/**
 * Test: Nette\Latte\Engine: <?xml test.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @phpini     short_open_tag=on
 */

use Nette\Latte;


require __DIR__ . '/../bootstrap.php';


function xml($v) { echo $v; }

$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

Assert::match(<<<EOD
<?xml version="1.0" ?>
12ok

EOD

, (string) $template->setSource(<<<EOD
<?xml version="1.0" ?>
<?php xml(1) ?>
<? xml(2) ?>
<?php echo 'ok' ?>
EOD
));
