<?php

/**
 * Test: Nette\Latte\Engine: {use ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



TestHelpers::purge(TEMP_DIR);


class MyMacros extends Latte\Macros\MacroSet
{
	public function __construct($parser)
	{
		parent::__construct($parser);
		$this->addMacro('my', 'echo "ok"');
	}
}


$template = new FileTemplate;
$template->setCacheStorage($cache = new MockCacheStorage(TEMP_DIR));
$template->setFile(__DIR__ . '/templates/use.latte');
$template->registerFilter(new Latte\Engine);

$result = $template->__toString(TRUE);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.html'), $result);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.phtml'), $cache->phtml['use.latte']);
