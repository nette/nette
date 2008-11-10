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



require_once dirname(__FILE__) . '/../Security/IAuthorizator.php';

require_once dirname(__FILE__) . '/../Object.php';



/**
 * Access control list (ACL) functionality and privileges management.
 *
 * This solution is mostly based on Zend_Acl (c) Zend Technologies USA Inc. (http://www.zend.com), new BSD license
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2005, 2007 Zend Technologies USA Inc.
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Security
 */
class Permission extends /*Nette\*/Object implements IAuthorizator
{
	/** Rule operation: add */
	const ADD = TRUE;

	/** Rule operation: remove */
	const REMOVE = FALSE;

	/** @var array  Role storage */
	protected $roles = array();

	/** @var array  Resource storage */
	protected $resources = array();

	/** @var array  Access Control List rules; whitelist (deny everything to all) by default */
	protected $rules = array(
		'allResources' => array(
			'allRoles' => array(
				'allPrivileges' => array(
					'type'   => self::DENY,
					'assert' => NULL,
				),
				'byPrivilege' => array(),
			),
			'byRole' => array(),
		),
		'byResource' => array(),
	);



	/********************* roles ****************d*g**/


	/**
	 * Adds a Role to the list.
	 *
	 * The $parents parameter may be a Role identifier (or array of identifiers)
	 * to indicate the Roles from which the newly added Role will directly inherit.
	 *
	 * In order to resolve potential ambiguities with conflicting rules inherited
	 * from different parents, the most recently added parent takes precedence over
	 * parents that were previously added. In other words, the first parent added
	 * will have the least priority, and the last parent added will have the
	 * highest priority.
	 *
	 * @param  string
	 * @param  string|array
	 * @throws Exception
	 * @return Permission  provides a fluent interface
	 */
	public function addRole($role, $parents = NULL)
	{
		if (!is_string($role) || $role === '') {
			throw new /*\*/InvalidArgumentException("Role must be a non-empty string.");
		}

		if ($this->hasRole($role)) {
			throw new /*\*/InvalidStateException("Role '$role' already exists in the list.");
		}

		$roleParents = array();

		if ($parents !== NULL) {
			if (!is_array($parents)) {
				$parents = array($parents);
			}

			foreach ($parents as $parent) {
				$this->needRole($parent);
				$roleParents[$parent] = TRUE;
				$this->roles[$parent]['children'][$role] = TRUE;
			}
		}

		$this->roles[$role] = array(
			'parents'  => $roleParents,
			'children' => array(),
		);

		return $this;
	}



	/**
	 * Returns TRUE if the Role exists in the list.
	 * @param  string
	 * @return bool
	 */
	public function hasRole($role)
	{
		return isset($this->roles[$role]);
	}



	/**
	 * Throws exception the Role doesn't exist in the list.
	 * @param  string
	 * @throws Exception
	 * @return bool
	 */
	protected function needRole($role)
	{
		if (!isset($this->roles[$role])) {
			throw new /*\*/InvalidStateException("Role '$role' does not exist.");
		}

		return TRUE;
	}



	/**
	 * Returns an array of an existing Role's parents.
	 *
	 * The parent Roles are ordered in this array by ascending priority.
	 * The highest priority parent Role, last in the array, corresponds with
	 * the parent Role most recently added.
	 *
	 * If the Role does not have any parents, then an empty array is returned.
	 *
	 * @param  string
	 * @return array
	 */
	public function getRoleParents($role)
	{
		$this->needRole($role);
		return array_keys($this->roles[$role]['parents']);
	}



	/**
	 * Returns TRUE if $role inherits from $inherit.
	 *
	 * If $onlyParents is TRUE, then $role must inherit directly from
	 * $inherit in order to return TRUE. By default, this method looks
	 * through the entire inheritance DAG to determine whether $role
	 * inherits from $inherit through its ancestor Roles.
	 *
	 * @param  string
	 * @param  string
	 * @param  boolean
	 * @throws Exception
	 * @return bool
	 */
	public function roleInheritsFrom($role, $inherit, $onlyParents = FALSE)
	{
		$this->needRole($role);
		$this->needRole($inherit);

		$inherits = isset($this->roles[$role]['parents'][$inherit]);

		if ($inherits || $onlyParents) {
			return $inherits;
		}

		foreach ($this->roles[$role]['parents'] as $parent => $foo) {
			if ($this->roleInheritsFrom($parent, $inherit)) {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * Removes the Role from the list.
	 *
	 * @param  string
	 * @throws Exception
	 * @return Permission  provides a fluent interface
	 */
	public function removeRole($role)
	{
		$this->needRole($role);

		foreach ($this->roles[$role]['children'] as $child => $foo)
			unset($this->roles[$child]['parents'][$role]);

		foreach ($this->roles[$role]['parents'] as $parent => $foo)
			unset($this->roles[$parent]['children'][$role]);

		unset($this->roles[$role]);

		foreach ($this->rules['allResources']['byRole'] as $roleCurrent => $rules) {
			if ($role === $roleCurrent) {
				unset($this->rules['allResources']['byRole'][$roleCurrent]);
			}
		}

		foreach ($this->rules['byResource'] as $resourceCurrent => $visitor) {
			foreach ($visitor['byRole'] as $roleCurrent => $rules) {
				if ($role === $roleCurrent) {
					unset($this->rules['byResource'][$resourceCurrent]['byRole'][$roleCurrent]);
				}
			}
		}

		return $this;
	}



	/**
	 * Removes all Roles from the list.
	 *
	 * @return Permission  provides a fluent interface
	 */
	public function removeAllRoles()
	{
		$this->roles = array();

		foreach ($this->rules['allResources']['byRole'] as $roleCurrent => $rules)
			unset($this->rules['allResources']['byRole'][$roleCurrent]);

		foreach ($this->rules['byResource'] as $resourceCurrent => $visitor) {
			foreach ($visitor['byRole'] as $roleCurrent => $rules) {
				unset($this->rules['byResource'][$resourceCurrent]['byRole'][$roleCurrent]);
			}
		}

		return $this;
	}



	/********************* resources ****************d*g**/



	/**
	 * Adds a Resource having an identifier unique to the list.
	 *
	 * @param  string
	 * @param  string
	 * @throws Exception
	 * @return Permission  provides a fluent interface
	 */
	public function addResource($resource, $parent = NULL)
	{
		if (!is_string($resource) || $resource === '') {
			throw new /*\*/InvalidArgumentException("Resource must be a non-empty string.");
		}

		if ($this->hasResource($resource)) {
			throw new /*\*/InvalidStateException("Resource '$resource' already exists in the list.");
		}

		$resourceParent = NULL;

		if ($parent !== NULL) {
			$this->needResource($parent);
			$this->resources[$parent]['children'][$resource] = TRUE;
		}

		$this->resources[$resource] = array(
			'parent'   => $parent,
			'children' => array()
		);

		return $this;
	}



	/**
	 * Returns TRUE if the Resource exists in the list.
	 * @param  string
	 * @return bool
	 */
	public function hasResource($resource)
	{
		return isset($this->resources[$resource]);
	}



	/**
	 * Throws exception the Resource doesn't exist in the list.
	 * @param  string
	 * @throws Exception
	 * @return bool
	 */
	protected function needResource($resource)
	{
		if (!isset($this->resources[$resource])) {
			throw new /*\*/InvalidStateException("Resource '$resource' does not exist.");
		}
	}



	/**
	 * Returns TRUE if $resource inherits from $inherit.
	 *
	 * If $onlyParents is TRUE, then $resource must inherit directly from
	 * $inherit in order to return TRUE. By default, this method looks
	 * through the entire inheritance tree to determine whether $resource
	 * inherits from $inherit through its ancestor Resources.
	 *
	 * @param  string
	 * @param  string
	 * @param  boolean
	 * @throws Exception
	 * @return bool
	 */
	public function resourceInheritsFrom($resource, $inherit, $onlyParent = FALSE)
	{
		$this->needResource($resource);
		$this->needResource($inherit);

		if ($this->resources[$resource]['parent'] === NULL) {
			return FALSE;
		}

		$parent = $this->resources[$resource]['parent'];
		if ($inherit === $parent) {
			return TRUE;
		} elseif ($onlyParent) {
			return FALSE;
		}

		while ($this->resources[$parent]['parent'] !== NULL) {
			$parent = $this->resources[$parent]['parent'];
			if ($inherit === $parent) {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * Removes a Resource and all of its children.
	 *
	 * @param  string
	 * @throws Exception
	 * @return Permission  provides a fluent interface
	 */
	public function removeResource($resource)
	{
		$this->needResource($resource);

		$parent = $this->resources[$resource]['parent'];
		if ($parent !== NULL) {
			unset($this->resources[$parent]['children'][$resource]);
		}

		$removed = array($resource);
		foreach ($this->resources[$resource]['children'] as $child => $foo) {
			$this->removeResource($child);
			$removed[] = $child;
		}

		foreach ($removed as $resourceRemoved) {
			foreach ($this->rules['byResource'] as $resourceCurrent => $rules) {
				if ($resourceRemoved === $resourceCurrent) {
					unset($this->rules['byResource'][$resourceCurrent]);
				}
			}
		}

		unset($this->resources[$resource]);

		return $this;
	}



	/**
	 * Removes all Resources.
	 *
	 * @return Permission  provides a fluent interface
	 */
	public function removeAllResources()
	{
		foreach ($this->resources as $resource => $foo) {
			foreach ($this->rules['byResource'] as $resourceCurrent => $rules) {
				if ($resource === $resourceCurrent) {
					unset($this->rules['byResource'][$resourceCurrent]);
				}
			}
		}

		$this->resources = array();
		return $this;
	}



	/********************* rules ****************d*g**/



	/**
	 * Adds an "allow" rule to the list.
	 *
	 * @param  string|array|self::ALL  roles
	 * @param  string|array|self::ALL  resources
	 * @param  string|array|self::ALL  privileges
	 * @param  IPermissionAssertion  $assert
	 * @return Permission  provides a fluent interface
	 */
	public function allow($roles = self::ALL, $resources = self::ALL, $privileges = self::ALL, IPermissionAssertion $assert = NULL)
	{
		return $this->setRule(self::ADD, self::ALLOW, $roles, $resources, $privileges, $assert);
	}



	/**
	 * Adds a "deny" rule to the list.
	 *
	 * @param  string|array|self::ALL  roles
	 * @param  string|array|self::ALL  resources
	 * @param  string|array|self::ALL  privileges
	 * @param  IPermissionAssertion  $assert
	 * @return Permission  provides a fluent interface
	 */
	public function deny($roles = self::ALL, $resources = self::ALL, $privileges = self::ALL, IPermissionAssertion $assert = NULL)
	{
		return $this->setRule(self::ADD, self::DENY, $roles, $resources, $privileges, $assert);
	}



	/**
	 * Removes "allow" permissions from the list.
	 *
	 * @param  string|array|self::ALL  roles
	 * @param  string|array|self::ALL  resources
	 * @param  string|array|self::ALL  privileges
	 * @return Permission  provides a fluent interface
	 */
	public function removeAllow($roles = self::ALL, $resources = self::ALL, $privileges = self::ALL)
	{
		return $this->setRule(self::REMOVE, self::ALLOW, $roles, $resources, $privileges);
	}



	/**
	 * Removes "deny" restrictions from the list.
	 *
	 * @param  string|array|self::ALL  roles
	 * @param  string|array|self::ALL  resources
	 * @param  string|array|self::ALL  privileges
	 * @return Permission  provides a fluent interface
	 */
	public function removeDeny($roles = self::ALL, $resources = self::ALL, $privileges = self::ALL)
	{
		return $this->setRule(self::REMOVE, self::DENY, $roles, $resources, $privileges);
	}



	/**
	 * Performs operations on Access Control List rules.
	 *
	 * The $operation parameter may be either Permission::ADD or Permission::REMOVE, depending on whether the
	 * user wants to add or remove a rule, respectively:
	 *
	 * ADD specifics:
	 *
	 *      A rule is added that would allow one or more Roles access to [certain $privileges
	 *      upon] the specified Resource(s).
	 *
	 * REMOVE specifics:
	 *
	 *      The rule is removed only in the context of the given Roles, Resources, and privileges.
	 *      Existing rules to which the remove operation does not apply would remain in the
	 *      Access Control List.
	 *
	 * The $type parameter may be either Permission::ALLOW or Permission::DENY, depending on whether the
	 * rule is intended to allow or deny permission, respectively.
	 *
	 * If either $roles or $resources is self::ALL, then the rule applies to all Roles or all Resources,
	 * respectively. Both may be self::ALL in order to work with the default rule of the ACL.
	 *
	 * The $privileges parameter may be used to further specify that the rule applies only
	 * to certain privileges upon the Resource(s) in question. This may be specified to be a single
	 * privilege with a string, and multiple privileges may be specified as an array of strings.
	 *
	 * If $assert is provided, then its assert() method must return TRUE in order for
	 * the rule to apply. If $assert is provided with $roles, $resources, and $privileges all
	 * equal to NULL, then a rule having a type of:
	 *
	 *      ALLOW will imply a type of DENY, and
	 *
	 *      DENY will imply a type of ALLOW
	 *
	 * when the rule's assertion fails. This is because the ACL needs to provide expected
	 * behavior when an assertion upon the default ACL rule fails.
	 *
	 * @param  bool  operation
	 * @param  bool  type
	 * @param  string|array|self::ALL  roles
	 * @param  string|array|self::ALL  resources
	 * @param  string|array|self::ALL  privileges
	 * @param  IPermissionAssertion assert
	 * @throws Exception
	 * @return Permission  provides a fluent interface
	 */
	public function setRule($operation, $type, $roles = self::ALL, $resources = self::ALL, $privileges = self::ALL, IPermissionAssertion $assert = NULL)
	{
		if ($type !== self::ALLOW && $type !== self::DENY) {
			throw new /*\*/InvalidArgumentException("Unsupported rule type; must be either 'Permission::ALLOW' or 'Permission::DENY'.");
		}

		// ensure that all specified Roles exist; normalize input to array of Roles or NULL
		if ($roles === self::ALL) {
			$roles = array(NULL);
		} else {
			if (!is_array($roles)) $roles = array($roles);

			foreach ($roles as $role) $this->needRole($role);
		}

		// ensure that all specified Resources exist; normalize input to array of Resources or NULL
		if ($resources === self::ALL) {
			$resources = array(NULL);
		} else {
			if (!is_array($resources)) $resources = array($resources);

			foreach ($resources as $resource) $this->needResource($resource);
		}

		// normalize privileges to array
		if ($privileges === self::ALL) {
			$privileges = array();
		} elseif (!is_array($privileges)) {
			$privileges = array($privileges);
		}


		if ($operation === self::ADD) { // add to the rules
			foreach ($resources as $resource) {
				foreach ($roles as $role) {
					$rules =& $this->getRules($resource, $role, TRUE);
					if (count($privileges) === 0) {
						$rules['allPrivileges']['type'] = $type;
						$rules['allPrivileges']['assert'] = $assert;
						if (!isset($rules['byPrivilege'])) {
							$rules['byPrivilege'] = array();
						}
					} else {
						foreach ($privileges as $privilege) {
							$rules['byPrivilege'][$privilege]['type'] = $type;
							$rules['byPrivilege'][$privilege]['assert'] = $assert;
						}
					}
				}
			}

		} elseif ($operation === self::REMOVE) { // remove from the rules
			foreach ($resources as $resource) {
				foreach ($roles as $role) {
					$rules =& $this->getRules($resource, $role);
					if ($rules === NULL) {
						continue;
					}
					if (count($privileges) === 0) {
						if ($resource === NULL && $role === NULL) {
							if ($type === $rules['allPrivileges']['type']) {
								$rules = array(
									'allPrivileges' => array(
										'type'   => self::DENY,
										'assert' => NULL
										),
									'byPrivilege' => array()
									);
							}
							continue;
						}
						if ($type === $rules['allPrivileges']['type']) {
							unset($rules['allPrivileges']);
						}
					} else {
						foreach ($privileges as $privilege) {
							if (isset($rules['byPrivilege'][$privilege]) &&
								$type === $rules['byPrivilege'][$privilege]['type']) {
								unset($rules['byPrivilege'][$privilege]);
							}
						}
					}
				}
			}
		} else {
			throw new /*\*/InvalidArgumentException("Unsupported operation; must be either 'Permission::ADD' or 'Permission::REMOVE'.");
		}

		return $this;
	}



	/**
	 * Returns TRUE if and only if the Role has access to the Resource.
	 *
	 * If either $role or $resource is self::ALL, then the query applies to all Roles or all Resources,
	 * respectively. Both may be self::ALL to query whether the ACL has a "blacklist" rule
	 * (allow everything to all). By default, Permission creates a "whitelist" rule (deny
	 * everything to all), and this method would return FALSE unless this default has
	 * been overridden (i.e., by executing $acl->allow()).
	 *
	 * If a $privilege is not provided, then this method returns FALSE if and only if the
	 * Role is denied access to at least one privilege upon the Resource. In other words, this
	 * method returns TRUE if and only if the Role is allowed all privileges on the Resource.
	 *
	 * This method checks Role inheritance using a depth-first traversal of the Role list.
	 * The highest priority parent (i.e., the parent most recently added) is checked first,
	 * and its respective parents are checked similarly before the lower-priority parents of
	 * the Role are checked.
	 *
	 * @param  string|self::ALL  role
	 * @param  string|self::ALL  resource
	 * @param  string|self::ALL  privilege
	 * @return bool
	 */
	public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL)
	{
		if ($role !== self::ALL) $this->needRole($role);

		if ($resource !== self::ALL) $this->needResource($resource);

		if ($privilege === self::ALL) {
			// query on all privileges
			do {
				// depth-first search on $role if it is not 'allRoles' pseudo-parent
				if ($role !== NULL && NULL !== ($result = $this->roleDFSAllPrivileges($role, $resource))) {
					return $result;
				}

				// look for rule on 'allRoles' psuedo-parent
				if (NULL !== ($rules = $this->getRules($resource, NULL))) {
					foreach ($rules['byPrivilege'] as $privilege => $rule) {
						if (self::DENY === ($ruleTypeOnePrivilege = $this->getRuleType($resource, NULL, $privilege))) {
							return FALSE;
						}
					}
					if (NULL !== ($ruleTypeAllPrivileges = $this->getRuleType($resource, NULL, NULL))) {
						return self::ALLOW === $ruleTypeAllPrivileges;
					}
				}

				// try next Resource
				$resource = $this->resources[$resource]['parent'];

			} while (TRUE); // loop terminates at 'allResources' pseudo-parent

		} else {
			// query on one privilege
			do {
				// depth-first search on $role if it is not 'allRoles' pseudo-parent
				if ($role !== NULL && NULL !== ($result = $this->roleDFSOnePrivilege($role, $resource, $privilege))) {
					return $result;
				}

				// look for rule on 'allRoles' pseudo-parent
				if (NULL !== ($ruleType = $this->getRuleType($resource, NULL, $privilege))) {
					return self::ALLOW === $ruleType;
				} elseif (NULL !== ($ruleTypeAllPrivileges = $this->getRuleType($resource, NULL, NULL))) {
					return self::ALLOW === $ruleTypeAllPrivileges;
				}

				// try next Resource
				$resource = $this->resources[$resource]['parent'];

			} while (TRUE); // loop terminates at 'allResources' pseudo-parent
		}
	}



	/********************* internals ****************d*g**/



	/**
	 * Performs a depth-first search of the Role DAG, starting at $role, in order to find a rule.
	 * allowing/denying $role access to all privileges upon $resource
	 *
	 * This method returns TRUE if a rule is found and allows access. If a rule exists and denies access,
	 * then this method returns FALSE. If no applicable rule is found, then this method returns NULL.
	 *
	 * @param  string  role
	 * @param  string  resource
	 * @return bool|NULL
	 */
	protected function roleDFSAllPrivileges($role, $resource)
	{
		$dfs = array(
			'visited' => array(),
			'stack'   => array(),
		);

		if (NULL !== ($result = $this->roleDFSVisitAllPrivileges($role, $resource, $dfs))) {
			return $result;
		}

		while (NULL !== ($role = array_pop($dfs['stack']))) {
			if (!isset($dfs['visited'][$role])) {
				if (NULL !== ($result = $this->roleDFSVisitAllPrivileges($role, $resource, $dfs))) {
					return $result;
				}
			}
		}

		return NULL;
	}



	/**
	 * Visits an $role in order to look for a rule allowing/denying $role access to all privileges upon $resource.
	 *
	 * This method returns TRUE if a rule is found and allows access. If a rule exists and denies access,
	 * then this method returns FALSE. If no applicable rule is found, then this method returns NULL.
	 *
	 * This method is used by the internal depth-first search algorithm and may modify the DFS data structure.
	 *
	 * @param  string  role
	 * @param  string  resource
	 * @param  array   dfs
	 * @return bool|NULL
	 */
	protected function roleDFSVisitAllPrivileges($role, $resource, &$dfs)
	{
		if (NULL !== ($rules = $this->getRules($resource, $role))) {
			foreach ($rules['byPrivilege'] as $privilege => $rule) {
				if (self::DENY === ($ruleTypeOnePrivilege = $this->getRuleType($resource, $role, $privilege))) {
					return FALSE;
				}
			}
			if (NULL !== ($ruleTypeAllPrivileges = $this->getRuleType($resource, $role, NULL))) {
				return self::ALLOW === $ruleTypeAllPrivileges;
			}
		}

		$dfs['visited'][$role] = TRUE;
		foreach ($this->roles[$role]['parents'] as $roleParent => $foo) {
			$dfs['stack'][] = $roleParent;
		}

		return NULL;
	}



	/**
	 * Performs a depth-first search of the Role DAG, starting at $role, in order to find a rule.
	 * allowing/denying $role access to a $privilege upon $resource
	 *
	 * This method returns TRUE if a rule is found and allows access. If a rule exists and denies access,
	 * then this method returns FALSE. If no applicable rule is found, then this method returns NULL.
	 *
	 * @param  string  role
	 * @param  string  resource
	 * @param  string  privilege
	 * @return bool|NULL
	 */
	protected function roleDFSOnePrivilege($role, $resource, $privilege)
	{
		$dfs = array(
			'visited' => array(),
			'stack'   => array(),
		);

		if (NULL !== ($result = $this->roleDFSVisitOnePrivilege($role, $resource, $privilege, $dfs))) {
			return $result;
		}

		while (NULL !== ($role = array_pop($dfs['stack']))) {
			if (!isset($dfs['visited'][$role])) {
				if (NULL !== ($result = $this->roleDFSVisitOnePrivilege($role, $resource, $privilege, $dfs))) {
					return $result;
				}
			}
		}

		return NULL;
	}



	/**
	 * Visits an $role in order to look for a rule allowing/denying $role access to a $privilege upon $resource.
	 *
	 * This method returns TRUE if a rule is found and allows access. If a rule exists and denies access,
	 * then this method returns FALSE. If no applicable rule is found, then this method returns NULL.
	 *
	 * This method is used by the internal depth-first search algorithm and may modify the DFS data structure.
	 *
	 * @param  string  role
	 * @param  string  resource
	 * @param  string  privilege
	 * @param  array   dfs
	 * @return bool|NULL
	 */
	protected function roleDFSVisitOnePrivilege($role, $resource, $privilege, &$dfs)
	{
		if (NULL !== ($ruleTypeOnePrivilege = $this->getRuleType($resource, $role, $privilege))) {
			return self::ALLOW === $ruleTypeOnePrivilege;
		}

		if (NULL !== ($ruleTypeAllPrivileges = $this->getRuleType($resource, $role, NULL))) {
			return self::ALLOW === $ruleTypeAllPrivileges;
		}

		$dfs['visited'][$role] = TRUE;
		foreach ($this->roles[$role]['parents'] as $roleParent => $foo)
			$dfs['stack'][] = $roleParent;

		return NULL;
	}



	/**
	 * Returns the rule type associated with the specified Resource, Role, and privilege.
	 * combination.
	 *
	 * If a rule does not exist or its attached assertion fails, which means that
	 * the rule is not applicable, then this method returns NULL. Otherwise, the
	 * rule type applies and is returned as either ALLOW or DENY.
	 *
	 * If $resource or $role is self::ALL, then this means that the rule must apply to
	 * all Resources or Roles, respectively.
	 *
	 * If $privilege is self::ALL, then the rule must apply to all privileges.
	 *
	 * If all three parameters are self::ALL, then the default ACL rule type is returned,
	 * based on whether its assertion method passes.
	 *
	 * @param  string|self::ALL  role
	 * @param  string|self::ALL  resource
	 * @param  string|self::ALL  privilege
	 * @return bool|NULL
	 */
	protected function getRuleType($resource, $role, $privilege)
	{
		// get the rules for the $resource and $role
		if (NULL === ($rules = $this->getRules($resource, $role))) {
			return NULL;
		}

		// follow $privilege
		if ($privilege === self::ALL) {
			if (isset($rules['allPrivileges'])) {
				$rule = $rules['allPrivileges'];
			} else {
				return NULL;
			}
		} elseif (!isset($rules['byPrivilege'][$privilege])) {
			return NULL;
		} else {
			$rule = $rules['byPrivilege'][$privilege];
		}

		// check assertion if necessary
		if ($rule['assert'] === NULL || $rule['assert']->assert($this, $role, $resource, $privilege)) {
			return $rule['type'];
		} elseif ($resource !== self::ALL || $role !== self::ALL || $privilege !== self::ALL) {
			return NULL;
		} elseif (self::ALLOW === $rule['type']) {
			return self::DENY;
		} else {
			return self::ALLOW;
		}
	}



	/**
	 * Returns the rules associated with a Resource and a Role, or NULL if no such rules exist.
	 *
	 * If either $resource or $role is self::ALL, this means that the rules returned are for all Resources or all Roles,
	 * respectively. Both can be self::ALL to return the default rule set for all Resources and all Roles.
	 *
	 * If the $create parameter is TRUE, then a rule set is first created and then returned to the caller.
	 *
	 * @param  string|self::ALL  resource
	 * @param  string|self::ALL  role
	 * @param  boolean  create
	 * @return array|NULL
	 */
	protected function & getRules($resource, $role, $create = FALSE)
	{
		// create a reference to NULL
		$null = NULL;
		$nullRef = & $null;

		// follow $resource
		do {
			if ($resource === self::ALL) {
				$visitor = & $this->rules['allResources'];
				break;
			}
			if (!isset($this->rules['byResource'][$resource])) {
				if (!$create) {
					return $nullRef;
				}
				$this->rules['byResource'][$resource] = array();
			}
			$visitor = & $this->rules['byResource'][$resource];
		} while (FALSE);


		// follow $role
		if ($role === self::ALL) {
			if (!isset($visitor['allRoles'])) {
				if (!$create) {
					return $nullRef;
				}
				$visitor['allRoles']['byPrivilege'] = array();
			}
			return $visitor['allRoles'];
		}

		if (!isset($visitor['byRole'][$role])) {
			if (!$create) {
				return $nullRef;
			}
			$visitor['byRole'][$role]['byPrivilege'] = array();
		}

		return $visitor['byRole'][$role];
	}

}





/**
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Security
 */
interface IPermissionAssertion
{
	/**
	 * Returns true if and only if the assertion conditions are met.
	 *
	 * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
	 * $role, $resource, or $privilege parameters are Permission::ALL, it means that the query applies to all Roles, Resources, or
	 * privileges, respectively.
	 *
	 * @param  Permission
	 * @param  string  role
	 * @param  string  resource
	 * @param  string  privilege
	 * @return bool
	 */
	public function assert(Permission $acl, $role = Permission::ALL, $resource = Permission::ALL, $privilege = Permission::ALL);
}
