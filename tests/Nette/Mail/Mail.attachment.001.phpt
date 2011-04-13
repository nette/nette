<?php

/**
 * Test: Nette\Mail\Message - attachments.
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Mail\Message;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';



$mail = new Message();
$mail->addAttachment('files/example.zip');
$mail->send();

Assert::match( <<<EOD
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
Message-ID: <%S%@localhost>
Content-Type: multipart/mixed;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit


----------%S%
Content-Type: application/octet-stream
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
$mail->addAttachment('files/example.zip')->setEncoding(Message::ENCODING_QUOTED_PRINTABLE);
$mail->send();

Assert::match( <<<EOD
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
Message-ID: <%S%@localhost>
Content-Type: multipart/mixed;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit


----------%S%
Content-Type: application/octet-stream
Content-Transfer-Encoding: quoted-printable
Content-Disposition: attachment; filename="example.zip"

PK=03=04=14=00=00=00=08=00'=882;&=91*g@=00=00=00A=00=00=00=0B=00=00=00versi=
on.txt=F3K-)IUp+J=CCM-=CF/=CAV0=D0=B3=D43T=D0(J-=CB,=CE=CC=CFSHI630O3P(J=CD=
IM,NMQ=00=8A=19=19=18X=EA=02=91=A1=85&/=17=00PK=01=02=14=00=14=00=00=00=08=
=00'=882;&=91*g@=00=00=00A=00=00=00=0B=00=00=00=00=00=00=00=00=00 =00=00=00=
=00=00=00=00version.txtPK=05=06=00=00=00=00=01=00=01=009=00=00=00i=00=00=00=
=00=00
----------%S%--
EOD
, TestMailer::$output );



$mail = new Message();
$name = iconv('UTF-8', 'WINDOWS-1250', 'files/žluťoučký.zip');
copy('files/example.zip', $name);
$mail->addAttachment($name);
unlink($name);
$mail->send();

Assert::match( <<<EOD
MIME-Version: 1.0
X-Mailer: Nette Framework
Date: %a%
Message-ID: <%S%@localhost>
Content-Type: multipart/mixed;
	boundary="--------%S%"

----------%S%
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit


----------%S%
Content-Type: application/octet-stream
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="luouk"

UEsDBBQAAAAIACeIMjsmkSpnQAAAAEEAAAALAAAAdmVyc2lvbi50eHTzSy0pSVVwK0rMTS3PL8pW
MNCz1DNU0ChKLcsszszPU0hJNjMwTzNQKErNSU0sTk1RAIoZGRhY6gKRoYUmLxcAUEsBAhQAFAAA
AAgAJ4gyOyaRKmdAAAAAQQAAAAsAAAAAAAAAAAAgAAAAAAAAAHZlcnNpb24udHh0UEsFBgAAAAAB
AAEAOQAAAGkAAAAAAA==
----------%S%--
EOD
, TestMailer::$output );
