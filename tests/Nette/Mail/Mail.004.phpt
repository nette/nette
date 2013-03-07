<?php

/**
 * Test: Nette\Mail\Message - HTML body.
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

$mail->setHTMLBody('<b><span>Příliš </span> žluťoučký <br>kůň</b>');

$mailer = new TestMailer();
$mailer->send($mail);

Assert::match( <<<EOD
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: John Doe <doe@example.com>
To: Lady Jane <jane@example.com>
Subject: Hello Jane!
Message-ID: <%S%@localhost>
Content-Type: multipart/alternative;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit

Příliš žluťoučký
kůň
----------%S%
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 8bit

<b><span>Příliš </span> žluťoučký <br>kůň</b>
----------%S%--
EOD
, TestMailer::$output );
