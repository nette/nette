<?php

/**
 * Test: Nette\Mail\Mail with template.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Mail\Mail,
	Nette\Environment,
	Nette\Templates\Template,
	Nette\Templates\LatteFilter;



require __DIR__ . '/../NetteTest/initialize.php';

require __DIR__ . '/Mail.inc';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);



$mail = new Mail();
$mail->addTo('Lady Jane <jane@example.com>');

$mail->htmlBody = new Template;
$mail->htmlBody->setFile('files/template.phtml');
$mail->htmlBody->registerFilter(new LatteFilter);

$mail->send();



__halt_compiler() ?>
