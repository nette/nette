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
	Nette\Diagnostics\Helpers;



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
		ob_start();
		list($services, $factories) = $this->getContainerData();
		require __DIR__ . '/templates/ContainerPanel.panel.phtml';
		return ob_get_clean();
	}



	/**
	 * @return Nette\Reflection\ClassType
	 */
	protected function getContainerReflection()
	{
		return Nette\Reflection\ClassType::from('Nette\DI\Container');
	}



	protected function getContainerData()
	{
		$services = array();
		$factories = array();
		$registry = $this->getContainerRegistry();
		$meta = $this->getContainerMeta();
		$classes = $this->getContainerClasses();

		foreach ($this->container->getReflection()->getMethods() as $method) {
			if (substr($method->getName(), 0, 13) == 'createService') {
				$name = lcfirst(substr($method->getName(), 13));
				$data = isset($registry[$name]) ? $registry[$name] : $method->getAnnotation('return');

				$services[] = array(
					'name' => $name,
					'classes' => isset($classes[$name]) ? $classes[$name] : array(),
					'created' => isset($registry[$name]) ? TRUE : FALSE,
					'data' => $data,
					'meta' => isset($meta[$name]) ? $meta[$name] : NULL,
				);
			} elseif (substr($method->getName(), 0, 6) == 'create') {
				$name = lcfirst(substr($method->getName(), 6));

				$factories[] = array(
					'name' => $name,
					'class' => $method->getAnnotation('return'),
					'meta' => isset($meta[$name]) ? $meta[$name] : NULL,
				);
			}
		}

		return array($services, $factories);
	}



	protected function getContainerRegistry()
	{
		$ref = $this->getContainerReflection()->getProperty('registry');
		$ref->setAccessible(TRUE);
		$registry = $ref->getValue($this->container);
		$ref->setAccessible(FALSE);
		return $registry;
	}



	protected function getContainerMeta()
	{
		$ref = $this->getContainerReflection()->getProperty('meta');
		$ref->setAccessible(TRUE);
		$meta = $ref->getValue($this->container);
		$ref->setAccessible(FALSE);
		return $meta;
	}



	protected function getContainerClasses()
	{
		$classes = array();

		foreach ($this->container->classes as $class => $name) {
			if (!isset($classes[$name])) $classes[$name] = array();
			$classes[$name][] = $class;
		}

		return $classes;
	}

}
