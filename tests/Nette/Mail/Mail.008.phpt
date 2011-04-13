<?php

/**
 * Test: Nette\Mail\Message - textual body.
 *
 * @author     Stork Dork
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Mail\Message;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';



$mail = new Message();

$mail->setFrom('John Doe <doe@example.com>');
$mail->addTo('Lady Jane <jane@example.foo>');
$mail->addTo('williams@example.foo');
$mail->addTo('Řehoř Řízek <rizek@example.foo>');
$mail->addTo('Luboš Smažák <smazak@example.foo>');
$mail->setSubject('Hello Jane!');

$mail->setBody('Žluťoučký kůň');

$mail->send();

Assert::match( <<<EOD
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: John Doe <doe@example.com>
To: Lady Jane <jane@example.foo>,williams@example.foo,=?UTF-8?B?xZg=?=
	=?UTF-8?B?ZWhvxZkgxZjDrXplaw==?= <rizek@example.foo>,
	=?UTF-8?B?THVib8WhIFNtYcW+w6Fr?= <smazak@example.foo>
Subject: Hello Jane!
Message-ID: <%S%@localhost>
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit

Žluťoučký kůň
EOD
, TestMailer::$output );
