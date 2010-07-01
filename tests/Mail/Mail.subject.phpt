<?php

/**
 * Test: Nette\Mail\Mail subject.
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



$mail = new Mail();
$mail->setSubject('Testovací ! <email> od žluťoučkého koně ...');
$mail->send();

T::note();

$mail = new Mail();
$mail->setSubject('veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemail');
$mail->send();

T::note();

$mail = new Mail();
$mail->setSubject('veryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryveryverylongemailšššššššššššššššš');
$mail->send();

T::note();

$mail = new Mail();
$mail->setSubject('==========================================================================================ššššššššššššššššš');
$mail->send();



__halt_compiler() ?>
