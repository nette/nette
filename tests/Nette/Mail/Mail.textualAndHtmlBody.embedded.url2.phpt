<?php

/**
 * Test: Nette\Mail\Message - textual and HTML body with embedded image mentioned in url construct without apostrophes in style attribute.
 *
 * @author     David Grudl
 */

use Nette\Mail\Message,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';


$mail = new Message();

$mail->setFrom('John Doe <doe@example.com>');
$mail->addTo('Lady Jane <jane@example.com>');
$mail->setSubject('Hello Jane!');

$mail->setBody('Sample text');

$mail->setHTMLBody('<b>Sample text</b> <table><tr><td style="background: url(background.png)"></td></tr></table>', __DIR__ . '/files');
// append automatically $mail->addEmbeddedFile('files/background.png');

$mailer = new TestMailer();
$mailer->send($mail);

Assert::matchFile(__DIR__ . '/Mail.textualAndHtmlBody.embedded.url2.expect', TestMailer::$output);
