<?php

/**
 * Test: Nette\Mail\Message - textual body and line longer than 990 characters.
 *
 * @author     Stork Dork
 * @package    Nette\Mail
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

$mail->setBody('Žluťoučký kůň' . str_repeat('x', 990));

$mailer = new TestMailer();
$mailer->send($mail);

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
Content-Transfer-Encoding: quoted-printable

=C5=BDlu=C5=A5ou=C4=8Dk=C3=BD k=C5=AF=C5=88xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
EOD
, TestMailer::$output );
