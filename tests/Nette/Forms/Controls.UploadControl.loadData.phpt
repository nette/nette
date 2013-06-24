<?php

/**
 * Test: Nette\Forms\Controls\UploadControl.
 *
 * @author     Martin Major
 * @package    Nette\Forms
 */

use Nette\Forms\Form,
	Nette\Http\FileUpload,
	Nette\Forms\Controls\UploadControl;



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
		'name' => 'invalidutf',
		'type' => '',
		'size' => 3013,
		'tmp_name' => 'C:\\PHP\\temp\\php1D5C.tmp',
		'error' => 0,
	)), $input->getValue() );
	Assert::true( $input->isFilled() );
});



test(function() { // missing data
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

	$input = $form->addUpload('invalid2');

	Assert::true( $form->isValid() );
	Assert::equal( new FileUpload(array()), $input->getValue() );
	Assert::false( $input->isFilled() );
});



test(function() { // validators
	$form = new Form;
	$input = $form->addUpload('avatar')
		->addRule($form::MAX_FILE_SIZE, NULL, 3000);

	Assert::false( UploadControl::validateFileSize($input, 3012) );
	Assert::true( UploadControl::validateFileSize($input, 3013) );

	Assert::true( UploadControl::validateMimeType($input, 'image/gif') );
	Assert::true( UploadControl::validateMimeType($input, 'image/*') );
	Assert::false( UploadControl::validateMimeType($input, 'text/*') );
	Assert::true( UploadControl::validateMimeType($input, 'text/css,image/*') );
	Assert::true( UploadControl::validateMimeType($input, array('text/css', 'image/*')) );
	Assert::false( UploadControl::validateMimeType($input, array()) );

	Assert::true( UploadControl::validateImage($input) );
});
