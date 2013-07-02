<?php

/**
 * Test: Nette\Latte\Compiler and macro methods calling order.
 *
 * @author     Miloslav Hůla
 * @package    Nette\Latte
 */

use Nette\Latte\IMacro,
	Nette\Latte\MacroNode,
	Nette\Latte\Parser,
	Nette\Latte\Compiler;


require __DIR__ . '/../bootstrap.php';


class MockMacro implements IMacro
{
	public $calls = array();

	public function initialize()
	{
		$this->calls[] = __FUNCTION__;
	}

	public function finalize()
	{
		$this->calls[] = __FUNCTION__;
	}

	public function nodeOpened(MacroNode $node)
	{
		$this->calls[] = array(__FUNCTION__, isset($node->htmlNode) ? $node->htmlNode->name : NULL, $node->closing, $node->prefix);
	}

	public function nodeClosed(MacroNode $node)
	{
		$this->calls[] = array(__FUNCTION__, isset($node->htmlNode) ? $node->htmlNode->name : NULL, $node->closing, $node->prefix);
	}
}

$latte = "
	{foo}Text{/foo}
	<div1>{foo}Text{/foo}</div1>
	<div2 n:foo>Text</div2>
	<div3 n:inner-foo>Text</div3>
	<div4 n:tag-foo>Text</div4>
";

$macro = new MockMacro;
$parser = new Parser;
$compiler = new Compiler;
$compiler->addMacro('foo', $macro);
$compiler->compile($parser->parse($latte));

Assert::same( array(
	'initialize',

	array('nodeOpened', NULL, FALSE, NULL),
	array('nodeClosed', NULL, TRUE, NULL),

	array('nodeOpened', 'div1', FALSE, NULL),
	array('nodeClosed', 'div1', TRUE, NULL),

	array('nodeOpened', 'div2', FALSE, 'none'),
	array('nodeClosed', 'div2', TRUE, 'none'),

	array('nodeOpened', 'div3', FALSE, 'inner'),
	array('nodeClosed', 'div3', TRUE, 'inner'),

	array('nodeOpened', 'div4', FALSE, 'tag'),
	array('nodeClosed', 'div4', TRUE, 'tag'),
	array('nodeOpened', 'div4', FALSE, 'tag'),
	array('nodeClosed', 'div4', TRUE, 'tag'),

	'finalize',
), $macro->calls );
