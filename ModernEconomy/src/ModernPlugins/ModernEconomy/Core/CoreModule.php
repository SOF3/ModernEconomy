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

	/** @var CurrencyProvider */
	private $currencyProvider;
	/** @var AccountProvider */
	private $accountProvider;
	/** @var OperationProvider */
	private $operationProvider;

	public static function create(Plugin $plugin, Logger $logger, DataBase $db, Configuration $configuration, MasterManager $masterManager, ?int $dbVersion) : Generator{
		$module = new CoreModule();
		$module->plugin = $plugin;
		$module->logger = $logger;
		$module->db = $db;
		$module->configuration = $configuration;

		$module->currencyProvider = yield from CurrencyProvider::create($db, $masterManager, $dbVersion);
		$module->accountProvider = yield from AccountProvider::create($db, $module->currencyProvider, $dbVersion);
		$module->operationProvider = yield from OperationProvider::create($db, $module->accountProvider, $dbVersion);

		return $module;
	}

	public function shutdown() : void{
	}

	private function __construct(){
	}
}
