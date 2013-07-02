<?php

/**
 * Test: Nette\Latte\Engine and Texy.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte;


require __DIR__ . '/../bootstrap.php';


class MockTexy
{
	function process($text, $singleLine = FALSE)
	{
		return '<pre>' . $text . '</pre>';
	}
}


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);
$template->registerHelper('texy', array(new MockTexy, 'process'));
$template->registerHelperLoader('Nette\Templating\Helpers::loader');

$template->hello = '<i>Hello</i>';
$template->people = array('John', 'Mary', 'Paul');

$result = (string) $template->setSource(<<<'EOD'
{block|lower|texy}
{$hello}
---------
- Escaped: {$hello}
- Non-escaped: {$hello|noescape}

- Escaped expression: {="<" . "b" . ">hello" . "</b>"}

- Non-escaped expression: {="<" . "b" . ">hello" . "</b>"|noescape}

- Array access: {$people[1]}

[* image.jpg *]
{/block}
EOD
);

Assert::match(<<<EOD
<pre>&lt;i&gt;hello&lt;/i&gt;
---------
- escaped: &lt;i&gt;hello&lt;/i&gt;
- non-escaped: <i>hello</i>

- escaped expression: &lt;b&gt;hello&lt;/b&gt;

- non-escaped expression: <b>hello</b>

- array access: mary

[* image.jpg *]
</pre>
EOD
, $result);
