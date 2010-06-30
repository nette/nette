<?php

/**
 * Test: Nette\Mail\Mail - textual body with attachment.
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

$mail->setFrom('John Doe <doe@example.com>');
$mail->addTo('Lady Jane <jane@example.com>');
$mail->setSubject('Hello Jane!');

$mail->setBody('Sample text');

$mail->addAttachment('files/example.zip');

$mail->send();



__halt_compiler() ?>
