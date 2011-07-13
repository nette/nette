<?php

/**
 * Common code for SimpleRouter test cases.
 */



class MockHttpRequest extends Nette\Web\HttpRequest
{

	public function __construct()
	{
		$_SERVER = array(
			'HTTP_HOST' => 'nette.org',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/file.php',
			'SCRIPT_FILENAME' => '/public_html/www/file.php',
			'SCRIPT_NAME' => '/file.php',
		);
	}



	public function setQuery(array $query)
	{
		$this->query = $query;
		return $this;
	}

}
