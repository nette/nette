<?php

/**
 * Test: Nette\Mail\Message valid email addresses.
 */

use Nette\Mail\Message;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';


$mail = new Message();

$mail->addTo('veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemail@example.com');

$mail->addCc('veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemail@example.com', 'John Doe');

$mail->addBcc('veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylong name <veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemail@example.com>');

$mailer = new TestMailer();
$mailer->send($mail);

Assert::match(<<<EOD
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
To: veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemail@example.com
Cc: John Doe
	 <veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemail@example.com>
Bcc: veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylong name
	 <veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemail@example.com>
Message-ID: <%a%@%a%>
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit
EOD
, TestMailer::$output);
