<?php

/**
 * Common code for Permission test cases.
 */



class MockAssertion implements Nette\Security\IPermissionAssertion
{
	private $returnValue;


	public function __construct($returnValue)
	{
		$this->returnValue = (bool) $returnValue;
	}


	public function assert(Nette\Security\Permission $acl, $roleId, $resourceId, $privilege)
	{
	return $this->returnValue;
	}

}