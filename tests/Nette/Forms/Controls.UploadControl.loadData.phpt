<?php

/**
 * Test: Nette\Forms\Controls\UploadControl.
 *
 * @author     Martin Major
 * @package    Nette\Forms
 */

use Nette\Forms\Form,
	Nette\Http\FileUpload,
	Nette\Forms\Validator;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';

$_FILES = array(
	'avatar' => array(
		'name' => 'license.txt',
		'type' => 'text/plain',
		'tmp_name' => __DIR__ . '/files/logo.gif',
		'error' => 0,
		'size' => 3013,
	),
	'container' => array(
		'name' => array('avatar' => "invalid\xAA\xAA\xAAutf"),
		'type' => array('avatar' => 'text/plain'),
		'tmp_name' => array('avatar' => 'C:\\PHP\\temp\\php1D5C.tmp'),
		'error' => array('avatar' => 0),
		'size' => array('avatar' => 3013),
	),
	'multiple' => array(
		'name' => array('avatar' => array('image.gif', 'image.png')),
		'type' => array('avatar' => array('a', 'b')),
		'tmp_name' => array('avatar' => array(__DIR__ . '/files/logo.gif', __DIR__ . '/files/logo.gif')),
		'error' => array('avatar' => array(0, 0)),
		'size' => array('avatar' => array(100, 200)),
	),
	'empty' => array(
		'name' => array(''),
		'type' => array(''),
		'tmp_name' => array(''),
		'error' => array(UPLOAD_ERR_NO_FILE),
		'size' => array(0),
	),
	'invalid1' => array(
		'name' => array(NULL),
		'type' => array(NULL),
		'tmp_name' => array(NULL),
		'error' => array(NULL),
		'size' => array(NULL),
	),
	'invalid2' => '',
);


test(function() {
	$form = new Form;
	$input = $form->addUpload('avatar');

	Assert::true( $form->isValid() );
	Assert::equal( new FileUpload(array(
		'name' => 'license.txt',
		'type' => '',
		'size' => 3013,
		'tmp_name' => __DIR__ . '/files/logo.gif',
		'error' => 0,
	)), $input->getValue() );
	Assert::true( $input->isFilled() );
});


test(function() { // container
	$form = new Form;
	$input = $form->addContainer('container')->addUpload('avatar');

	Assert::true( $form->isValid() );
	Assert::equal( new FileUpload(array(
		'name' => '',
		'type' => '',
		'size' => 3013,
		'tmp_name' => 'C:\\PHP\\temp\\php1D5C.tmp',
		'error' => 0,
	)), $input->getValue() );
	Assert::true( $input->isFilled() );
});


test(function() { // multiple (in container)
	$form = new Form;
	$input = $form->addContainer('multiple')->addUpload('avatar', NULL, TRUE);

	Assert::true( $form->isValid() );
	Assert::equal( array(new FileUpload(array(
		'name' => 'image.gif',
		'type' => '',
		'size' => 100,
		'tmp_name' => __DIR__ . '/files/logo.gif',
		'error' => 0,
	)), new FileUpload(array(
		'name' => 'image.png',
		'type' => '',
		'size' => 200,
		'tmp_name' => __DIR__ . '/files/logo.gif',
		'error' => 0,
	))), $input->getValue() );
	Assert::true( $input->isFilled() );
});


test(function() { // missing data
	$form = new Form;
	$input = $form->addUpload('empty', NULL, TRUE)
		->setRequired();

	Assert::false( $form->isValid() );
	Assert::equal( array(), $input->getValue() );
	Assert::false( $input->isFilled() );
});


test(function() { // empty data
	$form = new Form;
	$input = $form->addUpload('missing')
		->setRequired();

	Assert::false( $form->isValid() );
	Assert::equal( new FileUpload(array()), $input->getValue() );
	Assert::false( $input->isFilled() );
});


test(function() { // malformed data
	$form = new Form;
	$input = $form->addUpload('invalid1');

	Assert::true( $form->isValid() );
	Assert::equal( new FileUpload(array()), $input->getValue() );
	Assert::false( $input->isFilled() );

	$form = new Form;
	$input = $form->addUpload('invalid2');

	Assert::true( $form->isValid() );
	Assert::equal( new FileUpload(array()), $input->getValue() );
	Assert::false( $input->isFilled() );

	$form = new Form;
	$input = $form->addUpload('avatar', NULL, TRUE);

	Assert::true( $form->isValid() );
	Assert::equal( array(), $input->getValue() );
	Assert::false( $input->isFilled() );

	$form = new Form;
	$input = $form->addContainer('multiple')->addUpload('avatar');

	Assert::true( $form->isValid() );
	Assert::equal( new FileUpload(array()), $input->getValue() );
	Assert::false( $input->isFilled() );
});


test(function() { // validators
	$form = new Form;
	$input = $form->addUpload('avatar')
		->addRule($form::MAX_FILE_SIZE, NULL, 3000);

	Assert::false( Validator::validateFileSize($input, 3012) );
	Assert::true( Validator::validateFileSize($input, 3013) );

	Assert::true( Validator::validateMimeType($input, 'image/gif') );
	Assert::true( Validator::validateMimeType($input, 'image/*') );
	Assert::false( Validator::validateMimeType($input, 'text/*') );
	Assert::true( Validator::validateMimeType($input, 'text/css,image/*') );
	Assert::true( Validator::validateMimeType($input, array('text/css', 'image/*')) );
	Assert::false( Validator::validateMimeType($input, array()) );

	Assert::true( Validator::validateImage($input) );
});


test(function() { // validators on multiple files
	$form = new Form;
	$input = $form->addContainer('multiple')->addUpload('avatar', NULL, TRUE)
		->addRule($form::MAX_FILE_SIZE, NULL, 3000);

	Assert::false( Validator::validateFileSize($input, 150) );
	Assert::true( Validator::validateFileSize($input, 300) );

	Assert::true( Validator::validateMimeType($input, 'image/gif') );
	Assert::true( Validator::validateMimeType($input, 'image/*') );
	Assert::false( Validator::validateMimeType($input, 'text/*') );
	Assert::true( Validator::validateMimeType($input, 'text/css,image/*') );
	Assert::true( Validator::validateMimeType($input, array('text/css', 'image/*')) );
	Assert::false( Validator::validateMimeType($input, array()) );

	Assert::true( Validator::validateImage($input) );
});
