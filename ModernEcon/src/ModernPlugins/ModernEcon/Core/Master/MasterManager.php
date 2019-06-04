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

namespace ModernPlugins\ModernEcon\Core\Master;

use Generator;
use Logger;
use ModernPlugins\ModernEcon\Configuration\Configuration;
use ModernPlugins\ModernEcon\Core\PeerServer;
use ModernPlugins\ModernEcon\Generated\Queries;
use ModernPlugins\ModernEcon\Main;
use ModernPlugins\ModernEcon\Utils\AwaitDataConnector;
use ModernPlugins\ModernEcon\Utils\AwaitUtils;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use function count;
use function serialize;
use function unserialize;

final class MasterManager{
	/** @var Logger */
	private $logger;
	/** @var AwaitDataConnector */
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

	public function __construct(Logger $logger, AwaitDataConnector $connector, string $serverId){
		$this->logger = $logger;
		$this->connector = $connector;
		$this->serverId = $serverId;
	}

	public function executeInit() : Generator{
		yield $this->connector->executeGeneric(Queries::MODERNECON_CORE_LOCK_CREATE);
		yield $this->connector->executeGeneric(Queries::MODERNECON_CORE_LOCK_INIT);
	}

	public function executeIteration(?Configuration $configuration = null) : Generator{
		if($this->master){
			/** @var int $maintained */
			$maintained = yield $this->connector->executeChange(Queries::MODERNECON_CORE_LOCK_MAINTAIN, [
				"serverId" => $this->serverId,
			]);
			if($maintained === 0){
				$this->master = false;
				(new MasterReleaseEvent)->call();
			}
		}else{
			/** @var int $acquired */
			if($configuration !== null){
				$acquired = yield $this->connector->executeChange(Queries::MODERNECON_CORE_LOCK_ACQUIRE_WITH_CONFIG, [
					"serverId" => $this->serverId,
					"majorVersion" => Main::MAJOR_VERSION,
					"minorVersion" => Main::MINOR_VERSION,
					"config" => serialize($configuration),
				]);
			}else{
				$acquired = yield $this->connector->executeChange(Queries::MODERNECON_CORE_LOCK_ACQUIRE, [
					"serverId" => $this->serverId,
					"majorVersion" => Main::MAJOR_VERSION,
					"minorVersion" => Main::MINOR_VERSION,
				]);
			}
			if($acquired === 1){
				$this->master = true;
				(new MasterAcquisitionEvent())->call();
			}else{
				$rows = yield $this->connector->executeSelect(Queries::MODERNECON_CORE_LOCK_QUERY);
				if(count($rows) === 0){
					// This would happen if lock.acquire was executed more than 10 seconds ago,
					// and the master server lost its master status just after that
					// Let's continue to the next loop immediately so that we can try to acquire the lock again
					$this->logger->debug("Master acquisition failed but cannot query master successfully. MySQL lag?");
					return false;
				}
				$row = $rows[0];
				if($this->currentMaster === null || $row["master"] !== $this->currentMaster->getServerId()){
					$this->currentMaster = new PeerServer($row["master"], $row["majorVersion"], $row["minorVersion"]);
					if(!self::isVersionCompatible($this->currentMaster->getMajorVersion(), $this->currentMaster->getMinorVersion())){
						$this->logger->critical("ModernEcon network is acquired by a server of version {$row["majorVersion"]}.{$row["minorVersion"]}. Please use the same ModernEcon version on all servers. Shutting down server.");
						Server::getInstance()->shutdown();
						return true;
					}
					if($this->configHash !== null && $row["config_hash"] !== $this->configHash){
						$this->logger->critical("ModernEcon network configuration is updated. Please restart.");
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
		while(!$this->shutdown){
			$wait = yield $this->executeIteration();
			if($wait){
				yield AwaitUtils::sleep($scheduler, 30, AwaitUtils::SECONDS);
			}
		}
	}

	public function fetchMasterConfiguration() : Generator{
		$row = yield $this->connector->executeSelect(Queries::MODERNECON_CORE_LOCK_QUERY_CONFIG);
		$this->configHash = $row["config_hash"];
		return unserialize($row["config"], ["allowed_classes" => [Configuration::CONFIG_CLASSES]]);
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
			$released = yield $this->connector->executeChange(Queries::MODERNECON_CORE_LOCK_RELEASE, [
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
