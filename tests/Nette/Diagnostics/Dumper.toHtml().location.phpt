<?php

/**
 * Test: Nette\Diagnostics\Dumper::toHtml() with location
 */

use Nette\Diagnostics\Dumper,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Test
{
}

Assert::match( '<pre class="nette-dump" title="Dumper::toHtml( new Test, array(&quot;location&quot; =&gt; TRUE) ) )
in file %a% on line %d%"><span class="nette-dump-object" data-nette-href="editor:%a%">Test</span> <span class="nette-dump-hash">#%h%</span>
<small>in <a href="editor:%a%">%a%:%d%</a></small></pre>
', Dumper::toHtml( new Test, array("location" => TRUE) ) );
