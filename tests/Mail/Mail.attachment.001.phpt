<?php

/**
 * Test: Nette\Mail\Mail - attachments.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Mail\Mail;



require __DIR__ . '/../NetteTest/initialize.php';

require __DIR__ . '/Mail.inc';



output('ENCODING_BASE64');


$mail = new Mail();
$mail->addAttachment('files/example.zip');
$mail->send();


output('ENCODING_QUOTED_PRINTABLE');


$mail = new Mail();
$mail->addAttachment('files/example.zip')->setEncoding(Mail::ENCODING_QUOTED_PRINTABLE);
$mail->send();


output('nonASCII name');


$mail = new Mail();
$mail->addAttachment('files/žluouèký.zip');
$mail->send();



__halt_compiler() ?>
