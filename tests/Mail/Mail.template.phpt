<?php

/**
 * Test: Nette\Mail\Mail with template.
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Mail\Mail,
	Nette\Environment,
	Nette\Templates\Template,
	Nette\Templates\LatteFilter;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
TestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);



$mail = new Mail();
$mail->addTo('Lady Jane <jane@example.com>');

$mail->htmlBody = new Template;
$mail->htmlBody->setFile('files/template.phtml');
$mail->htmlBody->registerFilter(new LatteFilter);

$mail->send();

Assert::match(file_get_contents(__DIR__ . '/Mail.template.expect'), TestMailer::$output);
