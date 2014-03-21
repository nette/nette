<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\DatabaseDI;

use Nette,
	Nette\DI\ContainerBuilder;


/**
 * Nette Framework Database services.
 *
 * @author     David Grudl
 * @author     Jan Skrasek
 */
class DatabaseExtension extends Nette\DI\CompilerExtension
{

	public $databaseDefaults = array(
		'dsn' => NULL,
		'user' => NULL,
		'password' => NULL,
		'options' => NULL,
		'debugger' => TRUE,
		'explain' => TRUE,
		'reflection' => 'Nette\Database\Reflection\DiscoveredReflection',
		'autowired' => NULL,
	);


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$this->setupDatabase($container, $this->getConfig());
	}


	private function setupDatabase(ContainerBuilder $container, array $config)
	{
		if (isset($config['dsn'])) {
			$config = array('default' => $config);
		}

		$autowired = TRUE;
		foreach ((array) $config as $name => $info) {
			if (!is_array($info)) {
				continue;
			}
			$this->validate($info, $this->databaseDefaults, 'database');

			$info += array('autowired' => $autowired) + $this->databaseDefaults;
			$autowired = FALSE;

			foreach ((array) $info['options'] as $key => $value) {
				if (preg_match('#^PDO::\w+\z#', $key)) {
					unset($info['options'][$key]);
					$info['options'][constant($key)] = $value;
				}
			}

			$connection = $container->addDefinition($this->prefix("$name"))
				->setClass('Nette\Database\Connection', array($info['dsn'], $info['user'], $info['password'], $info['options']))
				->setAutowired($info['autowired'])
				->addSetup('Nette\Diagnostics\Debugger::getBlueScreen()->addPanel(?)', array(
					'Nette\Bridges\DatabaseTracy\ConnectionPanel::renderException'
				));

			if (!$info['reflection']) {
				$reflection = NULL;
			} elseif (is_string($info['reflection'])) {
				$reflection = new Nette\DI\Statement(preg_match('#^[a-z]+\z#', $info['reflection'])
					? 'Nette\Database\Reflection\\' . ucfirst($info['reflection']) . 'Reflection'
					: $info['reflection'], strtolower($info['reflection']) === 'discovered' ? array($connection) : array());
			} else {
				$tmp = Nette\DI\Compiler::filterArguments(array($info['reflection']));
				$reflection = reset($tmp);
			}

			$container->addDefinition($this->prefix("$name.context"))
				->setClass('Nette\Database\Context', array($connection, $reflection))
				->setAutowired($info['autowired']);

			if ($container->parameters['debugMode'] && $info['debugger']) {
				$connection->addSetup('Nette\Database\Helpers::createDebugPanel', array($connection, !empty($info['explain']), $name));
			}
		}
	}


	public function getConfig(array $defaults = NULL)
	{
		$config = $this->compiler->getConfig();
		$config = isset($config[$this->name]) ? $config[$this->name] : (isset($config['nette']['database']) ? $config['nette']['database'] : array());
		unset($config['services'], $config['factories']);
		return Nette\DI\Config\Helpers::merge($config, $this->compiler->getContainerBuilder()->expand($defaults));
	}


	private function validate(array $config, array $expected, $name)
	{
		if ($extra = array_diff_key($config, $expected)) {
			$extra = implode(", $name.", array_keys($extra));
			throw new Nette\InvalidStateException("Unknown option $name.$extra.");
		}
	}

}
