<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\Framework;

use Nette,
	Tracy,
	Tracy\Helpers,
	Tracy\BlueScreen,
	Latte;


/**
 * Initializes Tracy
 */
class TracyBridge
{

	public static function initialize()
	{
		$bar = Tracy\Debugger::getBar();
		$bar->info[] = 'Nette Framework ' . Nette\Framework::VERSION . ' (' . substr(Nette\Framework::REVISION, 8) . ')';

		$blueScreen = Tracy\Debugger::getBlueScreen();
		$blueScreen->collapsePaths[] = dirname(dirname(__DIR__));
		$blueScreen->info[] = 'Nette Framework ' . Nette\Framework::VERSION . ' (revision ' . Nette\Framework::REVISION . ')';

		$blueScreen->addPanel(function($e) {
			if ($e instanceof Latte\CompileException) {
				return array(
					'tab' => 'Template',
					'panel' => '<p>' . (is_file($e->sourceName) ? '<b>File:</b> ' . Helpers::editorLink($e->sourceName, $e->sourceLine) : htmlspecialchars($e->sourceName)) . '</p>'
						. ($e->sourceCode ? '<pre>' . BlueScreen::highlightLine(htmlspecialchars($e->sourceCode), $e->sourceLine) . '</pre>' : ''),
				);
			} elseif ($e instanceof Nette\Neon\Exception && preg_match('#line (\d+)#', $e->getMessage(), $m)) {
				if ($item = Helpers::findTrace($e->getTrace(), 'Nette\DI\Config\Adapters\NeonAdapter::load')) {
					return array(
						'tab' => 'NEON',
						'panel' => '<p><b>File:</b> ' . Helpers::editorLink($item['args'][0], $m[1]) . '</p>'
							. BlueScreen::highlightFile($item['args'][0], $m[1])
					);
				} elseif ($item = Helpers::findTrace($e->getTrace(), 'Nette\Neon\Decoder::decode')) {
					return array(
						'tab' => 'NEON',
						'panel' => BlueScreen::highlightPhp($item['args'][0], $m[1])
					);
				}
			}
		});
	}

}
