<?php

/**
 * Test: Nette\Http\Request invalid encoding.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http;


require __DIR__ . '/../bootstrap.php';


// Setup environment
define('INVALID', "\xC4\x76\xC5\xBE");
define('CONTROL_CHARACTERS', "A\x00B\x80C");

$_GET = array(
	'invalid' => INVALID,
	'control' => CONTROL_CHARACTERS,
	INVALID => '1',
	CONTROL_CHARACTERS => '1',
	'array' => array(INVALID => '1'),
);

$_POST = array(
	'invalid' => INVALID,
	'control' => CONTROL_CHARACTERS,
	INVALID => '1',
	CONTROL_CHARACTERS => '1',
	'array' => array(INVALID => '1'),
);

$_COOKIE = array(
	'invalid' => INVALID,
	'control' => CONTROL_CHARACTERS,
	INVALID => '1',
	CONTROL_CHARACTERS => '1',
	'array' => array(INVALID => '1'),
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
	'file2' => array(
		'name' => array(
			2 => INVALID,
		),

		'type' => array(
			2 => INVALID,
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
);

test(function() { // unfiltered data
	$factory = new Http\RequestFactory;
	$factory->setBinary();
	$request = $factory->createHttpRequest();

	Assert::same( $request->getQuery('invalid'), INVALID );
	Assert::same( $request->getQuery('control'), CONTROL_CHARACTERS );
	Assert::same( '1', $request->getQuery(INVALID) );
	Assert::same( '1', $request->getQuery(CONTROL_CHARACTERS) );
	Assert::same( '1', $request->query['array'][INVALID] );

	Assert::same( $request->getPost('invalid'), INVALID );
	Assert::same( $request->getPost('control'), CONTROL_CHARACTERS );
	Assert::same( '1', $request->getPost(INVALID) );
	Assert::same( '1', $request->getPost(CONTROL_CHARACTERS) );
	Assert::same( '1', $request->post['array'][INVALID] );

	Assert::same( $request->getCookie('invalid'), INVALID );
	Assert::same( $request->getCookie('control'), CONTROL_CHARACTERS );
	Assert::same( '1', $request->getCookie(INVALID) );
	Assert::same( '1', $request->getCookie(CONTROL_CHARACTERS) );
	Assert::same( '1', $request->cookies['array'][INVALID] );

	Assert::type( 'Nette\Http\FileUpload', $request->getFile(INVALID) );
	Assert::type( 'Nette\Http\FileUpload', $request->getFile(CONTROL_CHARACTERS) );
	Assert::type( 'Nette\Http\FileUpload', $request->files['file1'] );
});


test(function() { // filtered data
	$factory = new Http\RequestFactory;
	$request = $factory->createHttpRequest();

	Assert::same( '', $request->getQuery('invalid') );
	Assert::same( '', $request->getQuery('control') );
	Assert::null( $request->getQuery(INVALID) );
	Assert::null( $request->getQuery(CONTROL_CHARACTERS) );
	Assert::false( isset($request->query['array'][INVALID]) );

	Assert::same( '', $request->getPost('invalid') );
	Assert::same( '', $request->getPost('control') );
	Assert::null( $request->getPost(INVALID) );
	Assert::null( $request->getPost(CONTROL_CHARACTERS) );
	Assert::false( isset($request->post['array'][INVALID]) );

	Assert::same( '', $request->getCookie('invalid') );
	Assert::same( '', $request->getCookie('control') );
	Assert::null( $request->getCookie(INVALID) );
	Assert::null( $request->getCookie(CONTROL_CHARACTERS) );
	Assert::false( isset($request->cookies['array'][INVALID]) );

	Assert::null( $request->getFile(INVALID) );
	Assert::null( $request->getFile(CONTROL_CHARACTERS) );
	Assert::type( 'Nette\Http\FileUpload', $request->files['file1'] );
	Assert::same( '', $request->files['file1']->name );
	Assert::type( 'Nette\Http\FileUpload', $request->files['file2'][2] );
	Assert::same( '', $request->files['file2'][2]->name );
});
