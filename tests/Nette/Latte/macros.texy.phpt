<?php

/**
 * Test: Latte\Engine and Texy.
 *
 * @author     David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MockTexy
{
	function process($text, $singleLine = FALSE)
	{
		return '<pre>' . $text . '</pre>';
	}
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addFilter('texy', array(new MockTexy, 'process'));

$params['hello'] = '<i>Hello</i>';
$params['people'] = array('John', 'Mary', 'Paul');

$result = $latte->renderToString(<<<'EOD'
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
, $params);

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
