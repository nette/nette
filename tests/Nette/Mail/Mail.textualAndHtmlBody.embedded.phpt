<?php

/**
 * Test: Nette\Mail\Message - textual and HTML body with embedded image.
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

$mail->setHTMLBody('<b>Sample text</b> <img src="background.png">', __DIR__ . '/files');
// append automatically $mail->addEmbeddedFile('files/background.png');

$mailer = new TestMailer();
$mailer->send($mail);

Assert::match(file_get_contents(__DIR__ . '/Mail.textualAndHtmlBody.embedded.expect'), TestMailer::$output);
