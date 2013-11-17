<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\DI\Diagnostics;

use Nette,
	Nette\DI\Container,
	Nette\Diagnostics\Dumper; // used in templates


/**
 * Dependency injection container panel for Debugger Bar.
 *
 * @author     Patrik Votoček
 */
class ContainerPanel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{
	/** @var Nette\DI\Container */
	private $container;


	public function __construct(Container $container)
	{
		$this->container = $container;
	}


	/**
	 * Renders tab.
	 * @return string
	 */
	public function getTab()
	{
		ob_start();
		require __DIR__ . '/templates/ContainerPanel.tab.phtml';
		return ob_get_clean();
	}


	/**
	 * Renders panel.
	 * @return string
	 */
	public function getPanel()
	{
		$services = $factories = array();
		foreach (Nette\Reflection\ClassType::from($this->container)->getMethods() as $method) {
			if (preg_match('#^create(Service)?_*(.+)\z#', $method->getName(), $m)) {
				if ($m[1]) {
					$services[str_replace('__', '.', strtolower(substr($m[2], 0, 1)) . substr($m[2], 1))] = $method->getAnnotation('return');
				} elseif ($method->isPublic()) {
					$factories['create' . $m[2]] = $method->getAnnotation('return');
				}
			}
		}
		ksort($services);
		ksort($factories);
		$container = $this->container;
		$registry = $this->getContainerProperty('registry');
		$tags = array();
		$meta = $this->getContainerProperty('meta');
		if (isset($meta[Container::TAGS])) {
			foreach ($meta[Container::TAGS] as $tag => $tmp) {
				foreach ($tmp as $service => $val) {
					$tags[$service][$tag] = $val;
				}
			}
		}

		ob_start();
		require __DIR__ . '/templates/ContainerPanel.panel.phtml';
		return ob_get_clean();
	}


	private function getContainerProperty($name)
	{
		$prop = Nette\Reflection\ClassType::from('Nette\DI\Container')->getProperty($name);
		$prop->setAccessible(TRUE);
		return $prop->getValue($this->container);
	}

}
