<?php

/**
 * Test: Nette\Mail\Message with template.
 *
 * @author     David Grudl
 * @package    Nette\Mail
 */

use Nette\Latte,
	Nette\Mail\Message,
	Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';



$mail = new Message();
$mail->addTo('Lady Jane <jane@example.com>');

$mail->htmlBody = new FileTemplate;
$mail->htmlBody->setFile('files/template.phtml');
$mail->htmlBody->registerFilter(new Latte\Engine);

$mailer = new TestMailer();
$mailer->send($mail);

Assert::match(file_get_contents(__DIR__ . '/Mail.template.expect'), TestMailer::$output);
