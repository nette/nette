<?php

/**
 * Test: Nette\Mail\Message - textual body.
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

$mail->setBody('Žluťoučký kůň');

$mailer = new TestMailer();
$mailer->send($mail);

Assert::match( <<<EOD
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: John Doe <doe@example.com>
To: Lady Jane <jane@example.com>
Subject: Hello Jane!
Message-ID: <%S%@%S%>
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit

Žluťoučký kůň
EOD
, TestMailer::$output );
