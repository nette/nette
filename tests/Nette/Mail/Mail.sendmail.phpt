<?php

/**
 * Test: Nette\Mail\SendmailMailer
 *
 */

$subjectFromSendmail = '';

function fakemail($to, $subject, $message, $headers, $parameters = ''){
	global $subjectFromSendmail;
	$subjectFromSendmail = $subject;
}

$TestSendmailMailer = file_get_contents(__DIR__ . '/../../../Nette/Mail/SendmailMailer.php');
$TestSendmailMailer = preg_replace('/SendmailMailer/', 'TestSendmailMailer', $TestSendmailMailer);
$TestSendmailMailer = preg_replace("/'mail'/", "'fakemail'", $TestSendmailMailer);
file_put_contents(__DIR__ . '/../../../Nette/Mail/TestSendmailMailer.php',$TestSendmailMailer);

require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';

require __DIR__ . '/../../../Nette/Mail/TestSendmailMailer.php';

use Nette\Mail\TestSendmailMailer,
	Nette\Mail\Message,
	Tester\Assert;

$mailer = new TestSendmailMailer;

$mail = new Message();
$mail->setSubject('Dlouhý testovací ! <email> od žluťoučkého koně ...');
$mailer->send($mail);

Assert::match( '#\r\n\t#', $subjectFromSendmail );

unlink(__DIR__ . '/../../../Nette/Mail/TestSendmailMailer.php');
