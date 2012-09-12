<?php

/**
 * Test: Nette\Utils\MimeTypeDetector::fromString()
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\MimeTypeDetector;



require __DIR__ . '/../bootstrap.php';



Assert::same( 'image/gif', MimeTypeDetector::fromString(file_get_contents('files/images/logo.gif')) );
Assert::same( 'application/octet-stream', MimeTypeDetector::fromString(file_get_contents('files/bad.ppt')) );
