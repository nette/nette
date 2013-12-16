<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\DITracy;

use Nette,
	Nette\DI\Container,
	Tracy;


/**
 * Dependency injection container panel for Debugger Bar.
 *
 * @author     Patrik Votoček
 */
class ContainerPanel extends Nette\Object implements Tracy\IBarPanel
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
		$services = array();
		foreach (Nette\Reflection\ClassType::from($this->container)->getMethods() as $method) {
			if (preg_match('#^createService_*(.+)\z#', $method->getName(), $m)) {
				$services[str_replace('__', '.', strtolower(substr($m[1], 0, 1)) . substr($m[1], 1))] = $method->getAnnotation('return');
			}
		}
		ksort($services);
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
