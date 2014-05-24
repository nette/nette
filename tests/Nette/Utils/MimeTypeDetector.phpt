<?php

/**
 * Test: Nette\Utils\MimeTypeDetector::fromFile()
 */

use Nette\Utils\MimeTypeDetector,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( 'image/gif', MimeTypeDetector::fromFile('files/images/logo.gif') );
Assert::same( 'image/gif', MimeTypeDetector::fromString(file_get_contents('files/images/logo.gif')) );
