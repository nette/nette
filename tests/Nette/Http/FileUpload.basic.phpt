<?php

/**
 * Test: Nette\Http\FileUpload basic test.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\FileUpload;



require __DIR__ . '/../bootstrap.php';



$upload = new FileUpload(array(
	'name' => 'readme.txt',
	'type' => 'text/plain',
	'tmp_name' => __DIR__ . '/files/file.txt',
	'error' => 0,
	'size' => 209,
));

Assert::equal( 'readme.txt', $upload->getName() );
Assert::equal( 'readme.txt', $upload->getSanitizedName() );
Assert::equal( 209, $upload->getSize() );
Assert::equal( __DIR__ . '/files/file.txt', $upload->getTemporaryFile() );
Assert::equal( __DIR__ . '/files/file.txt', (string) $upload );
Assert::equal( 0, $upload->getError() );
Assert::true( $upload->isOk() );
Assert::false( $upload->isImage() );
Assert::equal( file_get_contents(__DIR__ . '/files/file.txt'), $upload->getContents() );



$upload = new FileUpload(array(
	'name' => '../.image.png',
	'type' => 'text/plain',
	'tmp_name' => __DIR__ . '/files/logo.png',
	'error' => 0,
	'size' => 209,
));

Assert::equal( '../.image.png', $upload->getName() );
Assert::equal( 'image.png', $upload->getSanitizedName() );
Assert::equal( 'image/png', $upload->getContentType() );
Assert::true( $upload->isImage() );
