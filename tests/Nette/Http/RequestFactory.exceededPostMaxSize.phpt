<?php

/**
 * Test: Nette\Http\RequestFactory workaround for exceeded post_max_size.
 *
 * @author     Jan-Sebastian Fabik
 * @package    Nette\Http
 */

use Nette\Http;



require __DIR__ . '/../bootstrap.php';



class StringStream
{
	/** @var string */
	public static $string;

	/** @var int */
	protected $offset = 0;



	public function stream_open()
	{
		return true;
	}



	public function stream_read($length)
	{
		$buffer = (string) substr(self::$string, $this->offset, $length);
		$this->offset += strlen($buffer);
		return $buffer;
	}



	public function stream_eof()
	{
		return $this->offset === strlen(self::$string);
	}
}



function testRequest(Http\RequestFactory $factory, $boundary, $input, $expectedPost = array(), $expectedFiles = array())
{
	StringStream::$string = $input;

	$_SERVER = array(
		'REQUEST_METHOD' => 'POST',
		'CONTENT_TYPE' => "multipart/form-data; boundary=$boundary",
	);
	$_POST = array();
	$_FILES = array();

	$request = $factory->createHttpRequest();
	Assert::equal($expectedPost, $request->getPost());
	Assert::equal($expectedFiles, $request->getFiles());
}



stream_wrapper_unregister('php');

stream_wrapper_register('php', 'StringStream');

$factory = new Http\RequestFactory;

testRequest($factory, 'BOUNDARY',
	"--BOUNDARY\r\n"
	. "Content-Disposition: form-data; name=\"first\"\r\n"
	. "\r\n"
	. "ok\r\n"
	. "--BOUNDARY\r\n"
	. "Content-Disposition: form-data; name=\"second\"\r\n"
	. "\r\n"
	. "ok\r\n"
	. "ok\r\n"
	. "\r\n"
	. "--BOUNDARY--\r\n",
	array('first' => 'ok', 'second' => "ok\r\nok\r\n"),
	array()
);

testRequest($factory, 'BOUNDARY',
	"--BOUNDARY\r\n"
	. "Content-Disposition: form-data; name=\"first\"\r\n"
	. "\r\n"
	. "ok\r\n"
	. "--BOUNDARY\r\n"
	. "Content-Disposition: form-data; name=\"second\"; filename=\"test.txt\"\r\n"
	. "Content-Type: text/plain\r\n"
	. "\r\n"
	. "sample text\r\n"
	. "\r\n"
	. "--BOUNDARY--\r\n",
	array('first' => 'ok'),
	array('second' =>
		new Http\FileUpload(array(
			'name' => 'test.txt',
			'type' => '',
			'size' => 0,
			'tmp_name' => '',
			'error' => UPLOAD_ERR_INI_SIZE,
		))
	)
);

stream_wrapper_restore('php');
