<?php

/**
 * Test: Nette\Mail\Mail with template.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Mail\Mail, Nette\Environment, Nette\Templates\Template, Nette\Templates\LatteFilter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Mail.inc';



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);



$mail = new Mail();
$mail->addTo('Lady Jane <jane@example.com>');

$mail->htmlBody = new Template;
$mail->htmlBody->setFile('files/template.phtml');
$mail->htmlBody->registerFilter(new LatteFilter);

$mail->send();



__halt_compiler() ?>
