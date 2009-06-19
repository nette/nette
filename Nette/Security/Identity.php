<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Security
 * @version    $Id$
 */

/*namespace Nette\Security;*/



require_once dirname(__FILE__) . '/../Security/IIdentity.php';

require_once dirname(__FILE__) . '/../Object.php';



/**
 * Default implementation of IIdentity.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Security
 *
 * @property   string $name
 * @property   mixed $id
 * @property   array $roles
 */
class Identity extends /*Nette\*/Object implements IIdentity
{
	/** @var string */
	private $name;

	/** @var array */
	private $roles;

	/** @var array */
	private $data;


	/**
	 * @param  string  identity name
	 * @param  mixed   roles
	 * @param  array   user data
	 */
	public function __construct($name, $roles = NULL, $data = NULL)
	{
		$this->setName($name);
		$this->setRoles((array) $roles);
		$this->data = (array) $data;
	}



	/**
	 * Sets the name of user.
	 * @param  string
	 * @return void
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
	}



	/**
	 * Returns the name of user.
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * Sets a list of roles that the user is a member of.
	 * @param  array
	 * @return void
	 */
	public function setRoles(array $roles)
	{
		$this->roles = $roles;
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
	 * Returns an user data.
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
		if ($key === 'name' || $key === 'roles') {
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
		if ($key === 'name' || $key === 'roles') {
			return parent::__get($key);

		} else {
			return $this->data[$key];
		}
	}

}
