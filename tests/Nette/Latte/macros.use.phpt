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

	/*5.2*static function install(Nette\Latte\Parser $parser)
	{
		return new self($parser);
	}*/
}


$template = new FileTemplate(__DIR__ . '/templates/use.latte');
$template->registerFilter(new Latte\Engine);

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.phtml"), codefix($template->compile()));
Assert::match(file_get_contents("$path.html"), $template->__toString(TRUE));
