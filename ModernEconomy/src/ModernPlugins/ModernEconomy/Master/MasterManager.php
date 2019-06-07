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

namespace ModernPlugins\ModernEconomy\Master;

use Generator;
use Logger;
use ModernPlugins\ModernEconomy\Configuration\Configuration;
use ModernPlugins\ModernEconomy\Generated\Queries;
use ModernPlugins\ModernEconomy\Main;
use ModernPlugins\ModernEconomy\Utils\AwaitUtils;
use ModernPlugins\ModernEconomy\Utils\DataBase;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use function serialize;
use function unserialize;

final class MasterManager{
	/** @var Logger */
	private $logger;
	/** @var DataBase */
	private $connector;
	/** @var string */
	private $serverId;

	/** @var bool */
	private $master = false;
	/** @var PeerServer|null */
	private $currentMaster = null;
	/** @var string|null */
	private $configHash = null;

	/** @var bool */
	private $shutdown = false;

	public function __construct(Logger $logger, DataBase $connector, string $serverId){
		$this->logger = $logger;
		$this->connector = $connector;
		$this->serverId = $serverId;
	}

	public function executeInit() : Generator{
		$this->logger->debug("Initializing");
		yield from $this->connector->executeGeneric(Queries::CORE_LOCK_CREATE);
		yield from $this->connector->executeGeneric(Queries::CORE_LOCK_INIT);
	}

	public function executeIteration(?Configuration $configuration = null) : Generator{
		if($this->master){
			/** @var int $maintained */
			$maintained = yield from $this->connector->executeChange(Queries::CORE_LOCK_MAINTAIN, [
				"serverId" => $this->serverId,
			]);
			if($maintained === 0){
				$this->master = false;
				(new MasterReleaseEvent)->call();
				$this->logger->info("Released master status");
			}
		}else{
			/** @var int $acquired */
			if($configuration !== null){
				$acquired = yield from $this->connector->executeChange(Queries::CORE_LOCK_ACQUIRE_WITH_CONFIG, [
					"serverId" => $this->serverId,
					"majorVersion" => Main::MAJOR_VERSION,
					"minorVersion" => Main::MINOR_VERSION,
					"config" => serialize($configuration),
				]);
			}else{
				$acquired = yield from $this->connector->executeChange(Queries::CORE_LOCK_ACQUIRE, [
					"serverId" => $this->serverId,
					"majorVersion" => Main::MAJOR_VERSION,
					"minorVersion" => Main::MINOR_VERSION,
				]);
			}
			if($acquired === 1){
				$this->master = true;
				(new MasterAcquisitionEvent())->call();
				$this->logger->info("Acquired master status");
			}else{
				$row = yield from $this->connector->executeSingleSelect(Queries::CORE_LOCK_QUERY);
				if($row === null){
					// This would happen if lock.acquire was executed more than 10 seconds ago,
					// and the master server lost its master status just after that
					// Let's continue to the next loop immediately so that we can try to acquire the lock again
					$this->logger->debug("Master acquisition failed but cannot query master successfully. MySQL lag?");
					return false;
				}
				if($this->currentMaster === null || $row["master"] !== $this->currentMaster->getServerId()){
					$this->currentMaster = new PeerServer($row["master"], $row["majorVersion"], $row["minorVersion"]);
					if(!self::isVersionCompatible($this->currentMaster->getMajorVersion(), $this->currentMaster->getMinorVersion())){
						$this->logger->critical("ModernEconomy network is acquired by a server of version {$row["majorVersion"]}.{$row["minorVersion"]}. Please use the same ModernEconomy version on all servers. Shutting down server.");
						Server::getInstance()->shutdown();
						return true;
					}
					if($this->configHash !== null && $row["config_hash"] !== $this->configHash){
						$this->logger->critical("ModernEconomy network configuration is updated. Please restart.");
						Server::getInstance()->shutdown();
						return true;
					}
					(new MasterChangeEvent($this->currentMaster))->call();
				}
			}
		}
		return true;
	}

	public function executeLoop(TaskScheduler $scheduler) : Generator{
		yield from AwaitUtils::sleep($scheduler, 5, AwaitUtils::SECONDS);
		while(!$this->shutdown){
			$wait = yield from $this->executeIteration();
			if($wait){
				yield from AwaitUtils::sleep($scheduler, 30, AwaitUtils::SECONDS);
			}
		}
	}

	public function fetchMasterConfiguration() : Generator{
		$row = yield from $this->connector->executeSingleSelect(Queries::CORE_LOCK_QUERY_CONFIG);
		$this->configHash = $row["config_hash"];
		return unserialize($row["config"], ["allowed_classes" => Configuration::CONFIG_CLASSES]);
	}

	public function isMaster() : bool{
		return $this->master;
	}

	/**
	 * This method only returns non-null when $this->master is false.
	 *
	 * @return PeerServer|null
	 */
	public function getCurrentMaster() : ?PeerServer{
		return $this->currentMaster;
	}

	public function shutdown() : Generator{
		$this->shutdown = true;
		if($this->master){
			$released = yield from $this->connector->executeChange(Queries::CORE_LOCK_RELEASE, [
				"serverId" => $this->serverId,
			]);
			if($released){
				(new MasterReleaseEvent)->call();
			}
		}
	}

	public static function isVersionCompatible(int $major, int $minor) : bool{
		return $major === Main::MAJOR_VERSION && $minor <= Main::MINOR_VERSION;
	}
}
