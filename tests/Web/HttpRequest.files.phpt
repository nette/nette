<?php

/**
 * Test: Nette\Web\HttpRequest files.
 *
 * @author     David Grudl
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\HttpRequest,
	Nette\Web\HttpUploadedFile;



require __DIR__ . '/../initialize.php';



// Setup environment
$_FILES = array(
	'file1' => array(
		'name' => 'readme.txt',
		'type' => 'text/plain',
		'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
		'error' => 0,
		'size' => 209,
	),

	'file2' => array(
		'name' => array(
			2 => 'license.txt',
		),

		'type' => array(
			2 => 'text/plain',
		),

		'tmp_name' => array(
			2 => 'C:\\PHP\\temp\\php1D5C.tmp',
		),

		'error' => array(
			2 => 0,
		),

		'size' => array(
			2 => 3013,
		),
	),

	'file3' => array(
		'name' => array(
			'y' => array(
				'z' => 'default.htm',
			),
			1 => 'logo.gif',
		),

		'type' => array(
			'y' => array(
				'z' => 'text/html',
			),
			1 => 'image/gif',
		),

		'tmp_name' => array(
			'y' => array(
				'z' => 'C:\\PHP\\temp\\php1D5D.tmp',
			),
			1 => 'C:\\PHP\\temp\\php1D5E.tmp',
		),

		'error' => array(
			'y' => array(
				'z' => 0,
			),
			1 => 0,
		),

		'size' => array(
			'y' => array(
				'z' => 26320,
			),
			1 => 3519,
		),
	),
);

$request = new HttpRequest;

Assert::true( $request->files['file1'] instanceof HttpUploadedFile );
Assert::true( $request->files['file2'][2] instanceof HttpUploadedFile );
Assert::true( $request->files['file3']['y']['z'] instanceof HttpUploadedFile );
Assert::true( $request->files['file3'][1] instanceof HttpUploadedFile );

Assert::false( isset($request->files['file0']) );
Assert::true( isset($request->files['file1']) );

Assert::null( $request->getFile('file1', 'a') );
