<?php

/**
 * Test: Nette\Latte\Engine and auto-safe URL.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Templating\Helpers::loader');
$template->url1 = 'javascript:alert(1)';
$template->url2 = ' javascript:alert(1)';
$template->url3 = 'data:text/html;base64,PHN2Zy9vbmxvYWQ9YWxlcnQoMik+';
$template->url4 = 'ok';
$template->url5 = '';

$template->setSource('
<a href={$url1} src="{$url1}" action={$url1} formaction={$url1} title={$url1}></a>
<a href={$url1|nosafeurl}></a>
<a href="http://nette.org?val={$url4}"></a>
<a data={$url1}></a>
<OBJECT DATA={$url1}></object>
<a HREF={$url2}></a>
<a href={$url3}></a>
<a href={$url4}>ok</a>
<a href={$url5}></a>
<a href={$url4|dataStream}></a>
<a href={$url4|dataStream|noSafeURL}></a>
<a href={$url4|dataStream|safeURL}></a>
');

Assert::match('
<a href="" src="" action="" formaction="" title="javascript:alert(1)"></a>
<a href="javascript:alert(1)"></a>
<a href="http://nette.org?val=ok"></a>
<a data="javascript:alert(1)"></a>
<OBJECT DATA=""></object>
<a HREF=""></a>
<a href=""></a>
<a href="ok">ok</a>
<a href=""></a>
<a href="data:%a%;base64,b2s="></a>
<a href="data:%a%;base64,b2s="></a>
<a href=""></a>
', (string) $template);



$template->setSource('
{contentType xml}
<a href={$url1} src="{$url1}" action={$url1} formaction={$url1} title={$url1}></a>
<object data={$url1}></object>
');

Assert::match('
<a href="javascript:alert(1)" src="javascript:alert(1)" action="javascript:alert(1)" formaction="javascript:alert(1)" title="javascript:alert(1)"></a>
<object data="javascript:alert(1)"></object>
', (string) $template);
