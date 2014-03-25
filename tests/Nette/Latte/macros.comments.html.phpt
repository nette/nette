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

require __DIR__ . '/Template.inc';


$latte = new Latte\Engine;
$latte->setContentType($latte::CONTENT_HTML);
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
