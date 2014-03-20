<?php

class MockUserStorage implements Nette\Security\IUserStorage
{
	private $auth = FALSE;
	private $identity;

	function setAuthenticated($state)
	{
		$this->auth = $state;
	}

	function isAuthenticated()
	{
		return $this->auth;
	}

	function setIdentity(Nette\Security\IIdentity $identity = NULL)
	{
		$this->identity = $identity;
	}

	function getIdentity()
	{
		return $this->identity;
	}

	function setExpiration($time, $flags = 0)
	{}

	function getLogoutReason()
	{}

}
