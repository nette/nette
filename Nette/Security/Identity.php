<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Security
 */

/*namespace Nette\Security;*/



require_once dirname(__FILE__) . '/../Security/IIdentity.php';

require_once dirname(__FILE__) . '/../FreezableObject.php';



/**
 * Default implementation of IIdentity.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Security
 *
 * @property   string $name
 * @property   mixed $id
 * @property   array $roles
 */
class Identity extends /*Nette\*/FreezableObject implements IIdentity
{
	/** @var mixed */
	private $id;

	/** @var array */
	private $roles;

	/** @var array */
	private $data;


	/**
	 * @param  mixed   identity ID
	 * @param  mixed   roles
	 * @param  array   user data
	 */
	public function __construct($id, $roles = NULL, $data = NULL)
	{
		$this->setId($id);
		$this->setRoles((array) $roles);
		$this->data = (array) $data;
	}



	/**
	 * Sets the ID of user.
	 * @param  mixed
	 * @return Identity  provides a fluent interface
	 */
	public function setId($id)
	{
		$this->updating();
		$this->id = $id;
		return $this;
	}



	/**
	 * Returns the ID of user.
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}



	/**
	 * Sets a list of roles that the user is a member of.
	 * @param  array
	 * @return Identity  provides a fluent interface
	 */
	public function setRoles(array $roles)
	{
		$this->updating();
		$this->roles = $roles;
		return $this;
	}



	/**
	 * Returns a list of roles that the user is a member of.
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roles;
	}



	/**
	 * Returns a user data.
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}



	/**
	 * Sets user data value.
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->updating();
		if ($key === 'id' || $key === 'roles') {
			parent::__set($key, $value);

		} else {
			$this->data[$key] = $value;
		}
	}



	/**
	 * Returns user data value.
	 * @param  string  property name
	 * @return mixed
	 */
	public function &__get($key)
	{
		if ($key === 'id' || $key === 'roles') {
			return parent::__get($key);

		} else {
			return $this->data[$key];
		}
	}

}
