<?php

/**
 * Test: Nette\Mail\Message subject.
 *
 * @author     David Grudl
 * @package    Nette\Mail
 */

use Nette\Mail\Message;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';


$mailer = new TestMailer();

$mail = new Message();
$mail->setSubject('Testovací ! <email> od žluťoučkého koně ...');
$mailer->send($mail);

Assert::match( 'MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
Subject: =?UTF-8?B?VGVzdG92YWPDrSAhIDxlbWFpbD4gb2Qgxb5sdcWlb3XEjWs=?=
	=?UTF-8?B?w6lobyBrb27EmyAuLi4=?=
Message-ID: <%S%@%S%>
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit
', TestMailer::$output );

$mail = new Message();
$mail->setSubject('veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemail');
$mailer->send($mail);

Assert::match( 'MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
Subject: =?UTF-8?B?dmVyeXZlcnl2ZXJ5dmVyeXZlcnl2ZXJ5dmVyeXZlcnl2ZXI=?=
	=?UTF-8?B?eXZlcnl2ZXJ5dmVyeXZlcnl2ZXJ5dmVyeXZlcnl2ZXJ5dmVyeXZlcnk=?=
	=?UTF-8?B?dmVyeXZlcnl2ZXJ5dmVyeXZlcnlsb25nZW1haWw=?=
Message-ID: <%S%@%S%>
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit
', TestMailer::$output );

$mail = new Message();
$mail->setSubject('veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemailšššššššššššššššš');
$mailer->send($mail);

Assert::match( 'MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
Subject: =?UTF-8?B?dmVyeXZlcnl2ZXJ5dmVyeXZlcnl2ZXJ5dmVyeXZlcnl2ZXI=?=
	=?UTF-8?B?eXZlcnl2ZXJ5dmVyeXZlcnl2ZXJ5dmVyeXZlcnl2ZXJ5dmVyeXZlcnk=?=
	=?UTF-8?B?dmVyeXZlcnlsb25nZW1haWzFocWhxaHFocWhxaHFocWhxaHFocWhxaE=?=
	=?UTF-8?B?xaHFocWhxaE=?=
Message-ID: <%S%@%S%>
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit
', TestMailer::$output );

$mail = new Message();
$mail->setSubject('==========================================================================================ššššššššššššššššš');
$mailer->send($mail);

Assert::match( 'MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
Subject: =?UTF-8?B?PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0=?=
	=?UTF-8?B?PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0=?=
	=?UTF-8?B?PT09PT09PT09PT09PT3FocWhxaHFocWhxaHFocWhxaHFocWhxaHFoQ==?=
	=?UTF-8?B?xaHFocWhxaE=?=
Message-ID: <%S%@%S%>
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit

', TestMailer::$output );
