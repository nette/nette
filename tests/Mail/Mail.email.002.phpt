<?php

/**
 * Test: Nette\Mail\Mail valid email addresses.
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

$mail->setFrom('Žluťoučký kůň <kun1@example.com>');

$mail->addReplyTo('Žluťoučký kůň <kun2@example.com>');
$mail->addReplyTo('doe2@example.com', 'John Doe');

$mail->addTo('Žluťoučký "kůň" <kun3@example.com>');
$mail->addTo('doe3@example.com', "John 'jd' Doe");

$mail->addCc('Nette\\Mail <nette@example.com>');
$mail->addCc('doe4@example.com', 'John Doe');

$mail->addBcc('Žluťoučký kůň <kun5@example.com>');
$mail->addBcc('doe5@example.com');

$mail->setReturnPath('doe@example.com');

$mail->send();



__halt_compiler() ?>

------EXPECT------
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
From: =?UTF-8?Q?=C5=BDlu=C5=A5ou=C4=8Dk=C3=BD=20k=C5=AF=C5=88?=
	 <kun1@example.com>
Reply-To: =?UTF-8?Q?=C5=BDlu=C5=A5ou=C4=8Dk=C3=BD=20k=C5=AF=C5=88?=
	 <kun2@example.com>,John Doe <doe2@example.com>
To: =?UTF-8?Q?=C5=BDlu=C5=A5ou=C4=8Dk=C3=BD=20"k=C5=AF=C5=88"?=
	 <kun3@example.com>,John 'jd' Doe <doe3@example.com>
Cc: Nette\Mail <nette@example.com>,John Doe <doe4@example.com>
Bcc: =?UTF-8?Q?=C5=BDlu=C5=A5ou=C4=8Dk=C3=BD=20k=C5=AF=C5=88?=
	 <kun5@example.com>,doe5@example.com
Return-Path: doe@example.com
Message-ID: <%a%@%a%>
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit
