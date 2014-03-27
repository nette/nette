<?php

/**
 * Test: Nette\Latte\Engine: general HTML test.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setTempDirectory(TEMP_DIR);
$latte->addFilter('translate', 'strrev');
$latte->addFilter('join', 'implode');

$params['hello'] = '<i>Hello</i>';
$params['xss'] = 'some&<>"\'/chars';
$params['people'] = array('John', 'Mary', 'Paul', ']]> <!--');
$params['menu'] = array('about', array('product1', 'product2'), 'contact');
$params['el'] = Html::el('div')->title('1/2"');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	$latte->compile(__DIR__ . '/templates/general.latte')
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/general.latte',
		$params
	)
);
