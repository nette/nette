<?php

/**
 * Test: Nette\Mail\Message - textual and HTML body with attachment.
 *
 * @author     David Grudl
 * @package    Nette\Mail
 */

use Nette\Mail\Message;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';



$mail = new Message();

$mail->setFrom('John Doe <doe@example.com>');
$mail->addTo('Lady Jane <jane@example.com>');
$mail->setSubject('Hello Jane!');

$mail->setBody('Sample text');

$mail->setHTMLBody('<b>Sample text</b>');

$mail->addAttachment(__DIR__ . '/files/example.zip', NULL, 'application/zip');

$mailer = new TestMailer();
$mailer->send($mail);

Assert::match(file_get_contents(__DIR__ . '/Mail.005.expect'), TestMailer::$output);
