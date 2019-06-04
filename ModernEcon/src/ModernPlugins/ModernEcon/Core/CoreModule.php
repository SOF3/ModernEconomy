<?php

/*
 * ModernEcon
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

namespace ModernPlugins\ModernEcon\Core;

use Logger;
use ModernPlugins\ModernEcon\Core\Master\MasterManager;
use ModernPlugins\ModernEcon\Utils\AwaitDataConnector;
use pocketmine\plugin\Plugin;
use PrefixedLogger;
use SOFe\AwaitGenerator\Await;

final class CoreModule{
	/** @var Plugin */
	private $plugin;
	/** @var Logger */
	private $logger;
	/** @var AwaitDataConnector */
	private $connector;

	/** @var MasterManager */
	private $masterManager;

	public function __construct(Plugin $plugin, Logger $logger, AwaitDataConnector $connector, string $serverId){
		$this->plugin = $plugin;
		$this->logger = $logger;
		$this->connector = $connector;

		$this->masterManager = new MasterManager(new PrefixedLogger($logger, "Master"), $connector, $serverId);
		Await::g2c($this->masterManager->execute($plugin->getScheduler()));
	}
}
