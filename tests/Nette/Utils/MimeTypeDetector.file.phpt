<?php

/**
 * Test: Nette\Utils\MimeTypeDetector::fromFile()
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\MimeTypeDetector;



require __DIR__ . '/../bootstrap.php';



Assert::same( 'image/gif', MimeTypeDetector::fromFile('files/images/logo.gif') );
Assert::same( 'application/octet-stream', MimeTypeDetector::fromFile('files/bad.ppt') );
