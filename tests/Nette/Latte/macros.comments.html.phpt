<?php

/**
 * Test: Nette\Latte\Engine: comments HTML test.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$params['gt'] = '>';
$params['dash'] = '-';
$params['basePath'] = '/www';

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/comments.latte',
		$params
	)
);
