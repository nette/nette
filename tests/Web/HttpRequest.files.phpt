<?php

/**
 * Test: Nette\Web\HttpRequest files.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\HttpRequest;*/
/*use Nette\Web\HttpUploadedFile;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



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

dump( $request->files['file1'] instanceof HttpUploadedFile ); // TRUE
dump( $request->files['file2'][2] instanceof HttpUploadedFile ); // TRUE
dump( $request->files['file3']['y']['z'] instanceof HttpUploadedFile ); // TRUE
dump( $request->files['file3'][1] instanceof HttpUploadedFile ); // TRUE

dump( isset($request->files['file0']) ); // False
dump( isset($request->files['file1']) ); // True

dump( $request->getFile('file1', 'a') ); // Null



__halt_compiler();

------EXPECT------
bool(TRUE)

bool(TRUE)

bool(TRUE)

bool(TRUE)

bool(FALSE)

bool(TRUE)

NULL
