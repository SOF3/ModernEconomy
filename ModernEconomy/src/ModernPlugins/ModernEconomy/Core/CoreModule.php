<?php

/*
 * ModernEconomy
 *
 * Copyright (C) 2019 ModernPlugins
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace ModernPlugins\ModernEconomy\Core;

use Generator;
use Logger;
use ModernPlugins\ModernEconomy\Configuration\Configuration;
use ModernPlugins\ModernEconomy\Core\Currency\CurrencyManager;
use ModernPlugins\ModernEconomy\Master\MasterManager;
use ModernPlugins\ModernEconomy\Utils\DataBase;
use pocketmine\plugin\Plugin;

final class CoreModule{
	/** @var Plugin */
	private $plugin;
	/** @var Logger */
	private $logger;
	/** @var DataBase */
	private $db;
	/** @var Configuration */
	private $configuration;

	private $currencyManager;

	public static function create(Plugin $plugin, Logger $logger, DataBase $db, Configuration $configuration, MasterManager $masterManager, bool $creating) : Generator{
		$module = new CoreModule();
		$module->plugin = $plugin;
		$module->logger = $logger;
		$module->db = $db;
		$module->configuration = $configuration;

		$module->currencyManager = yield CurrencyManager::create($db, $masterManager, $creating);

		return $module;
	}

	public function shutdown() : void{
	}

	private function __construct(){
	}
}
