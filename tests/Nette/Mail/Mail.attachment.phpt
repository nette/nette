<?php

/**
 * Test: Nette\Mail\Message - attachments.
 *
 * @author     David Grudl
 * @package    Nette\Mail
 */

use Nette\Mail\Message;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';


$mailer = new TestMailer();

$mail = new Message();
$mail->addAttachment(__DIR__ . '/files/example.zip', NULL, 'application/zip');
$mailer->send($mail);

Assert::match( <<<EOD
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
Message-ID: <%S%@%S%>
Content-Type: multipart/mixed;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit


----------%S%
Content-Type: application/zip
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="example.zip"

UEsDBBQAAAAIACeIMjsmkSpnQAAAAEEAAAALAAAAdmVyc2lvbi50eHTzSy0pSVVwK0rMTS3PL8pW
MNCz1DNU0ChKLcsszszPU0hJNjMwTzNQKErNSU0sTk1RAIoZGRhY6gKRoYUmLxcAUEsBAhQAFAAA
AAgAJ4gyOyaRKmdAAAAAQQAAAAsAAAAAAAAAAAAgAAAAAAAAAHZlcnNpb24udHh0UEsFBgAAAAAB
AAEAOQAAAGkAAAAAAA==
----------%S%--
EOD
, TestMailer::$output );


$mail = new Message();
$mail->addAttachment(__DIR__ . '/files/example.zip', NULL, 'application/zip')
	->setEncoding(Message::ENCODING_QUOTED_PRINTABLE);
$mailer->send($mail);

Assert::match( <<<EOD
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
Message-ID: <%S%@%S%>
Content-Type: multipart/mixed;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit


----------%S%
Content-Type: application/zip
Content-Transfer-Encoding: quoted-printable
Content-Disposition: attachment; filename="example.zip"

PK=03=04=14=00=00=00=08=00'=882;&=91*g@=00=00=00A=00=00=00=0B=00=00=00versi=%A%00
----------%S%--
EOD
, TestMailer::$output );


$mail = new Message();
$name = iconv('UTF-8', 'WINDOWS-1250', 'žluťoučký.zip');
$mail->addAttachment($name, file_get_contents(__DIR__ . '/files/example.zip'), 'application/zip');
$mailer->send($mail);

Assert::match( <<<EOD
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
Message-ID: <%S%@%S%>
Content-Type: multipart/mixed;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit


----------%S%
Content-Type: application/zip
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="%S?%"

UEsDBBQAAAAIACeIMjsmkSpnQAAAAEEAAAALAAAAdmVyc2lvbi50eHTzSy0pSVVwK0rMTS3PL8pW
MNCz1DNU0ChKLcsszszPU0hJNjMwTzNQKErNSU0sTk1RAIoZGRhY6gKRoYUmLxcAUEsBAhQAFAAA
AAgAJ4gyOyaRKmdAAAAAQQAAAAsAAAAAAAAAAAAgAAAAAAAAAHZlcnNpb24udHh0UEsFBgAAAAAB
AAEAOQAAAGkAAAAAAA==
----------%S%--
EOD
, TestMailer::$output );
