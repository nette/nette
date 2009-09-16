<?php

/**
 * Test: Nette\Web\HttpRequest invalid encoding.
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
define('INVALID', "\x76\xC4\xC5\xBE");
define('CONTROL_CHARACTERS', "A\x00B\x80C");

$_GET = array(
	'invalid' => INVALID,
	'control' => CONTROL_CHARACTERS,
	INVALID => 1,
	CONTROL_CHARACTERS => 1,
	'array' => array(INVALID => 1),
);

$_POST = array(
	'invalid' => INVALID,
	'control' => CONTROL_CHARACTERS,
	INVALID => 1,
	CONTROL_CHARACTERS => 1,
	'array' => array(INVALID => 1),
);

$_COOKIE = array(
	'invalid' => INVALID,
	'control' => CONTROL_CHARACTERS,
	INVALID => 1,
	CONTROL_CHARACTERS => 1,
	'array' => array(INVALID => 1),
);

$_FILES = array(
	INVALID => array(
		'name' => 'readme.txt',
		'type' => 'text/plain',
		'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
		'error' => 0,
		'size' => 209,
	),
	CONTROL_CHARACTERS => array(
		'name' => 'readme.txt',
		'type' => 'text/plain',
		'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
		'error' => 0,
		'size' => 209,
	),
	'file1' => array(
		'name' => INVALID,
		'type' => 'text/plain',
		'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
		'error' => 0,
		'size' => 209,
	),
);

output('==> unfiltered data');
$request = new HttpRequest;

dump( $request->getQuery('invalid') === INVALID ); // TRUE
dump( $request->getQuery('control') === CONTROL_CHARACTERS ); // TRUE
dump( $request->getQuery(INVALID) ); // '1'
dump( $request->getQuery(CONTROL_CHARACTERS) ); // '1'
dump( $request->query['array'][INVALID] ); // '1'

dump( $request->getPost('invalid') === INVALID ); // TRUE
dump( $request->getPost('control') === CONTROL_CHARACTERS ); // TRUE
dump( $request->getPost(INVALID) ); // '1'
dump( $request->getPost(CONTROL_CHARACTERS) ); // '1'
dump( $request->post['array'][INVALID] ); // '1'

dump( $request->getCookie('invalid') === INVALID ); // TRUE
dump( $request->getCookie('control') === CONTROL_CHARACTERS ); // TRUE
dump( $request->getCookie(INVALID) ); // '1'
dump( $request->getCookie(CONTROL_CHARACTERS) ); // '1'
dump( $request->cookies['array'][INVALID] ); // '1'

dump( $request->getFile(INVALID) instanceof HttpUploadedFile ); // TRUE
dump( $request->getFile(CONTROL_CHARACTERS) instanceof HttpUploadedFile ); // TRUE
dump( $request->files['file1'] instanceof HttpUploadedFile ); // TRUE


output('==> filtered data');
$request->setEncoding('UTF-8');

dump( $request->getQuery('invalid') ); // "v\xc5\xbe"
dump( $request->getQuery('control') ); // 'ABC'
dump( $request->getQuery(INVALID) ); // Null
dump( $request->getQuery(CONTROL_CHARACTERS) ); // Null
dump( isset($request->query['array'][INVALID]) ); // False

dump( $request->getPost('invalid') ); // "v\xc5\xbe"
dump( $request->getPost('control') ); // 'ABC'
dump( $request->getPost(INVALID) ); // Null
dump( $request->getPost(CONTROL_CHARACTERS) ); // Null
dump( isset($request->post['array'][INVALID]) ); // False

dump( $request->getCookie('invalid') ); // "v\xc5\xbe"
dump( $request->getCookie('control') ); // 'ABC'
dump( $request->getCookie(INVALID) ); // Null
dump( $request->getCookie(CONTROL_CHARACTERS) ); // Null
dump( isset($request->cookies['array'][INVALID]) ); // False

dump( $request->getFile(INVALID) ); // Null
dump( $request->getFile(CONTROL_CHARACTERS) ); // Null
dump( $request->files['file1'] instanceof HttpUploadedFile ); // TRUE
dump( $request->files['file1']->name ); // "v\xc5\xbe"



__halt_compiler();

------EXPECT------
==> unfiltered data

bool(TRUE)

bool(TRUE)

int(1)

int(1)

int(1)

bool(TRUE)

bool(TRUE)

int(1)

int(1)

int(1)

bool(TRUE)

bool(TRUE)

int(1)

int(1)

int(1)

bool(TRUE)

bool(TRUE)

bool(TRUE)

==> filtered data

string(3) "vž"

string(3) "ABC"

NULL

NULL

bool(FALSE)

string(3) "vž"

string(3) "ABC"

NULL

NULL

bool(FALSE)

string(3) "vž"

string(3) "ABC"

NULL

NULL

bool(FALSE)

NULL

NULL

bool(TRUE)

string(3) "vž"
