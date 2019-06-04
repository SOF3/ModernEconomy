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
use ModernPlugins\ModernEcon\Core\PeerServer;
use ModernPlugins\ModernEcon\Generated\Queries;
use ModernPlugins\ModernEcon\Utils\AwaitDataConnector;
use ModernPlugins\ModernEcon\Utils\AwaitUtils;
use pocketmine\scheduler\TaskScheduler;
use function count;

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

	public function __construct(Logger $logger, AwaitDataConnector $connector, string $serverId){
		$this->logger = $logger;
		$this->connector = $connector;
		$this->serverId = $serverId;
	}

	public function execute(TaskScheduler $scheduler) : Generator{
		yield $this->connector->executeGeneric(Queries::MODERNECON_CORE_LOCK_CREATE);
		yield $this->connector->executeGeneric(Queries::MODERNECON_CORE_LOCK_INIT);
		yield $this->executeLoop($scheduler);
	}

	private function executeLoop(TaskScheduler $scheduler) : Generator{
		while(true){
			if($this->master){
				/** @var int $maintained */
				$maintained = yield $this->connector->executeChange(Queries::MODERNECON_CORE_LOCK_ACQUIRE, [
					"serverId" => $this->serverId,
				]);
				if($maintained === 0){
					$this->master = false;
					(new MasterLossEvent)->call();
				}
			}else{
				/** @var int $acquired */
				$acquired = yield $this->connector->executeChange(Queries::MODERNECON_CORE_LOCK_ACQUIRE, [
					"serverId" => $this->serverId,
				]);
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
						continue;
					}
					$row = $rows[0];
					if($this->currentMaster === null || $row["master"] !== $this->currentMaster->getServerId()){
						$this->currentMaster = new PeerServer($row["master"], $row["majorVersion"], $row["minorVersion"]);
						(new MasterChangeEvent($this->currentMaster))->call();
					}
				}
			}
			yield AwaitUtils::sleep($scheduler, 30, AwaitUtils::SECONDS);
		}
	}

	/**
	 * This method only returns non-null when $this->master is false.
	 *
	 * @return PeerServer|null
	 */
	public function getCurrentMaster() : ?PeerServer{
		return $this->currentMaster;
	}
}
