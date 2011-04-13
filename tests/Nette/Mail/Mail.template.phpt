<?php

/**
 * Test: Nette\Mail\Message with template.
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Latte,
	Nette\Mail\Message,
	Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
TestHelpers::purge(TEMP_DIR);



$mail = new Message();
$mail->addTo('Lady Jane <jane@example.com>');

$mail->htmlBody = new FileTemplate;
$mail->htmlBody->setFile('files/template.phtml');
$mail->htmlBody->registerFilter(new Latte\Engine);

$mail->send();

Assert::match(file_get_contents(__DIR__ . '/Mail.template.expect'), TestMailer::$output);
