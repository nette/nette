<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\Tracy;

use Nette;


/**
 * Session panel for Debugger Bar.
 *
 * @author     David Grudl
 */
class HttpSessionPanel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{

	/**
	 * Renders tab.
	 * @return string
	 */
	public function getTab()
	{
		ob_start();
		require __DIR__ . '/templates/HttpSessionPanel.tab.phtml';
		return ob_get_clean();
	}


	/**
	 * Renders panel.
	 * @return string
	 */
	public function getPanel()
	{
		ob_start();
		require __DIR__ . '/templates/HttpSessionPanel.panel.phtml';
		return ob_get_clean();
	}

}
