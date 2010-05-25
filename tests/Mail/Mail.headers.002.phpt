<?php

/**
 * Test: Nette\Mail\Mail valid headers.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Mail\Mail;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Mail.inc';



$mail = new Mail();

$mail->setFrom('John Doe <doe@example.com>');

$mail->addTo('Lady Jane <jane@example.com>');
$mail->addCc('jane@example.info');
$mail->addBcc('bcc@example.com');
$mail->addReplyTo('reply@example.com');
$mail->setReturnPath('doe@example.com');

$mail->setSubject('Hello Jane!');
$mail->setPriority(Mail::HIGH);

$mail->setHeader('X-Gmail-Label', 'love');

$mail->send();



__halt_compiler() ?>

------EXPECT------
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: John Doe <doe@example.com>
To: Lady Jane <jane@example.com>
Cc: jane@example.info
Bcc: bcc@example.com
Reply-To: reply@example.com
Return-Path: doe@example.com
Subject: Hello Jane!
X-Priority: 1
X-Gmail-Label: love
Message-ID: <%a%@%a%>
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit
