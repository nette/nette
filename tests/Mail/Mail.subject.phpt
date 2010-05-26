<?php

/**
 * Test: Nette\Mail\Mail subject.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Mail\Mail;



require __DIR__ . '/../NetteTest/initialize.php';

require __DIR__ . '/Mail.inc';



$mail = new Mail();
$mail->setSubject('Testovací ! <email> od žluťoučkého koně ...');
$mail->send();

output();

$mail = new Mail();
$mail->setSubject('veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemail');
$mail->send();

output();

$mail = new Mail();
$mail->setSubject('veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemailšššššššššššššššš');
$mail->send();

output();

$mail = new Mail();
$mail->setSubject('==========================================================================================ššššššššššššššššš');
$mail->send();



__halt_compiler() ?>
