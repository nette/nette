<?php

/**
 * Test: Nette\Mail\Mail - attachments.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Mail\Mail;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Mail.inc';



T::note('ENCODING_BASE64');


$mail = new Mail();
$mail->addAttachment('files/example.zip');
$mail->send();


T::note('ENCODING_QUOTED_PRINTABLE');


$mail = new Mail();
$mail->addAttachment('files/example.zip')->setEncoding(Mail::ENCODING_QUOTED_PRINTABLE);
$mail->send();


T::note('nonASCII name');


$mail = new Mail();
$mail->addAttachment('files/žluouèký.zip');
$mail->send();



__halt_compiler() ?>
