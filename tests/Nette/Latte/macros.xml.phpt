<?php

/**
 * Test: Nette\Latte\Engine: {contentType application/xml}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


restore_error_handler();


$latte = new Latte\Engine;
$latte->setContentType($latte::CONTENT_XML);

$params['hello'] = '<i>Hello</i>';
$params['id'] = ':/item';
$params['people'] = array('John', 'Mary', 'Paul', ']]> <!--');
$params['comment'] = 'test -- comment';
$params['netteHttpResponse'] = new Nette\Http\Response;
$params['el'] = Html::el('div')->title('1/2"');

Assert::matchFile(
	__DIR__ . '/expected/macros.xml.phtml',
	$latte->compile(__DIR__ . '/templates/xml.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/macros.xml.html',
	$latte->renderToString(
		__DIR__ . '/templates/xml.latte',
		$params
	)
);
