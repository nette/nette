<?php

/**
 * Test: Nette\Templating\Helpers::escapeHtml
 */

use Nette\Templating\Helpers,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( '', Helpers::escapeHtml(NULL) );
Assert::same( '1', Helpers::escapeHtml(1) );
Assert::same( '&lt;br&gt;', Helpers::escapeHtml('<br>') );
Assert::same( '&lt; &amp; &#039; &quot; &gt;', Helpers::escapeHtml('< & \' " >') );
Assert::same( '&lt; &amp; \' " &gt;', Helpers::escapeHtml('< & \' " >', ENT_NOQUOTES) );
Assert::same( '<br>', Helpers::escapeHtml(Nette\Utils\Html::el('br'), ENT_NOQUOTES) );

// mXSS
Assert::same( '`hello ', Helpers::escapeHtml('`hello') );
Assert::same( '`hello', Helpers::escapeHtml('`hello', ENT_NOQUOTES) );
Assert::same( '`hello&quot;', Helpers::escapeHtml('`hello"') );
Assert::same( "`hello&#039;", Helpers::escapeHtml("`hello'") );
