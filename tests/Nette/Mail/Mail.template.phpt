<?php

/**
 * Test: Nette\Mail\Message with template.
 *
 * @author     David Grudl
 * @package    Nette\Mail
 */

use Nette\Latte,
	Nette\Mail\Message,
	Nette\Templating\FileTemplate,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';


$mail = new Message();
$mail->addTo('Lady Jane <jane@example.com>');

$mail->htmlBody = new FileTemplate;
$mail->htmlBody->setFile('files/template.phtml');
$mail->htmlBody->registerFilter(new Latte\Engine);

$mail->send();

Assert::matchFile(__DIR__ . '/Mail.template.expect', TestMailer::$output);
