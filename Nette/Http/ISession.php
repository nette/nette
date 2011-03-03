<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Web;

use Nette;



/**
 * Provides access to session namespaces as well as session settings and management methods.
 *
 * @author     David Grudl
 */
interface ISession
{

	/**
	 * Starts and initializes session data.
	 * @throws \InvalidStateException
	 * @return void
	 */
	function start();

	/**
	 * Has been session started?
	 * @return bool
	 */
	function isStarted();

	/**
	 * Ends the current session and store session data.
	 * @return void
	 */
	function close();

	/**
	 * Destroys all data registered to a session.
	 * @return void
	 */
	function destroy();

	/**
	 * Does session exists for the current request?
	 * @return bool
	 */
	function exists();

	/**
	 * Regenerates the session ID.
	 * @throws \InvalidStateException
	 * @return void
	 */
	function regenerateId();

	/**
	 * Returns the current session ID. Don't make dependencies, can be changed for each request.
	 * @return string
	 */
	function getId();

	/**
	 * Sets the session name to a specified one.
	 * @param  string
	 * @return ISession  provides a fluent interface
	 */
	function setName($name);

	/**
	 * Gets the session name.
	 * @return string
	 */
	function getName();

	/********************* namespaces management ****************d*g**/

	/**
	 * Returns specified session namespace.
	 * @param  string
	 * @param  string
	 * @return SessionNamespace
	 * @throws \InvalidArgumentException
	 */
	function getNamespace($namespace, $class = 'Nette\Web\SessionNamespace');

	/**
	 * Checks if a session namespace exist and is not empty.
	 * @param  string
	 * @return bool
	 */
	function hasNamespace($namespace);

	/**
	 * Iteration over all namespaces.
	 * @return \ArrayIterator
	 */
	function getIterator();

	/**
	 * Cleans and minimizes meta structures.
	 * @return void
	 */
	function clean();

	/**
	 * Sets session options.
	 * @param  array
	 * @return ISession  provides a fluent interface
	 * @throws \NotSupportedException
	 * @throws \InvalidStateException
	 */
	function setOptions(array $options);

	/**
	 * Returns all session options.
	 * @return array
	 */
	function getOptions();

	/**
	 * Sets the amount of time allowed between requests before the session will be terminated.
	 * @param  string|int|DateTime  time, value 0 means "until the browser is closed"
	 * @return ISession  provides a fluent interface
	 */
	function setExpiration($time);

	/**
	 * Sets the session cookie parameters.
	 * @param  string  path
	 * @param  string  domain
	 * @param  bool    secure
	 * @return ISession  provides a fluent interface
	 */
	function setCookieParams($path, $domain = NULL, $secure = NULL);

	/**
	 * Returns the session cookie parameters.
	 * @return array  containing items: lifetime, path, domain, secure, httponly
	 */
	function getCookieParams();

	/**
	 * Sets path of the directory used to save session data.
	 * @return ISession  provides a fluent interface
	 */
	function setSavePath($path);
	
}
