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

namespace ModernPlugins\ModernEconomy\Player;

use Generator;
use Logger;
use ModernPlugins\ModernEconomy\Configuration\Configuration;
use ModernPlugins\ModernEconomy\Core\Account\AccountOwnerType;
use ModernPlugins\ModernEconomy\Core\Account\AccountType;
use ModernPlugins\ModernEconomy\Core\CoreModule;
use ModernPlugins\ModernEconomy\DataBaseMigration;
use ModernPlugins\ModernEconomy\UI\UString\HardUString;
use ModernPlugins\ModernEconomy\Utils\DataBase;
use pocketmine\plugin\Plugin;

final class PlayerModule{
	/** @var Plugin */
	private $plugin;
	/** @var Logger */
	private $logger;
	/** @var DataBase */
	private $db;
	/** @var Configuration */
	private $configuration;
	/** @var CoreModule */
	private $coreModule;

	public static function create(Plugin $plugin, Logger $logger, DataBase $db, Configuration $configuration, CoreModule $coreModule, ?DataBaseMigration $migration) : Generator{
		false && yield;

		if($migration !== null){
			if($migration->getFromVersion() <= DataBaseMigration::EMPTY_MIGRATE_VERSION){

			}
		}

		$coreModule->getAccountOwnerTypeRegistry()->register(new AccountOwnerType("moderneconomy.player.player", new HardUString("Player")));
		$coreModule->getAccountTypeRegistry()->register(new AccountType("moderneconomy.player.cash", new HardUString("Cash")));

		$module = new PlayerModule;
		$module->plugin = $plugin;
		$module->logger = $logger;
		$module->db = $db;
		$module->configuration = $configuration;
		$module->coreModule = $coreModule;
		return $module;
	}

	public function shutdown() : Generator{
		false && yield;
	}

	private function __construct(){
	}
}
