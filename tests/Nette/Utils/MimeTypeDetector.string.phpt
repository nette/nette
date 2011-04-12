<?php

/**
 * Test: Nette\MimeTypeDetector::fromString()
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\MimeTypeDetector;



require __DIR__ . '/../bootstrap.php';



Assert::same( 'image/gif', MimeTypeDetector::fromString(file_get_contents('files/images/logo.gif')) );
Assert::same( 'application/octet-stream', MimeTypeDetector::fromString(file_get_contents('files/bad.ppt')) );
