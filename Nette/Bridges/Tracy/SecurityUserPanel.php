<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\Tracy;

use Nette;


/**
 * User panel for Debugger Bar.
 *
 * @author     David Grudl
 */
class SecurityUserPanel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{
	/** @var Nette\Security\User */
	private $user;


	public function __construct(Nette\Security\User $user)
	{
		$this->user = $user;
	}


	/**
	 * Renders tab.
	 * @return string
	 */
	public function getTab()
	{
		ob_start();
		require __DIR__ . '/templates/SecurityUserPanel.tab.phtml';
		return ob_get_clean();
	}


	/**
	 * Renders panel.
	 * @return string
	 */
	public function getPanel()
	{
		ob_start();
		require __DIR__ . '/templates/SecurityUserPanel.panel.phtml';
		return ob_get_clean();
	}

}
