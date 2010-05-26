<?php

/**
 * Test: Nette\Mail\Mail - textual and HTML body with embedded image and attachment.
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

$mail->setFrom('John Doe <doe@example.com>');
$mail->addTo('Lady Jane <jane@example.com>');
$mail->setSubject('Hello Jane!');

$mail->setBody('Sample text');

$mail->setHTMLBody('<b>Sample text</b> <img src="background.png">', __DIR__ . '/files');
// append automatically $mail->addEmbeddedFile('files/background.png');

$mail->addAttachment('files/example.zip');

$mail->send();



__halt_compiler() ?>
