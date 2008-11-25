<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
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
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Security
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
	 * @param  array   roles
	 * @param  array   user data
	 */
	public function __construct($name, array $roles = NULL, $data = NULL)
	{
		$this->name = (string) $name;
		$this->roles = (array) $roles;
		$this->data = (array) $data;
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
	 * Returns a list of roles that the user is a member of.
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roles;
	}



	/**
	 * @return array
	 */
	public function &__get($key)
	{
		if ($key === 'name' || $key === 'roles') {
			return parent::__get($key);

		} elseif (isset($key, $this->data)) {
			return $this->data[$key];
		}
	}

}
