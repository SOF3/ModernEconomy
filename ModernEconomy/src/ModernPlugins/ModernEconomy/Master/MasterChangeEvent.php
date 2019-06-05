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

use ModernPlugins\ModernEconomy\Core\PeerServer;
use pocketmine\event\Event;

/**
 * This event is called when a new master server is detected.
 *
 * This event is also called when the server starts up without successfully acquiring the master status
 * because of another active master server.
 */
class MasterChangeEvent extends Event{
	/** @var PeerServer */
	private $newMaster;

	public function __construct(PeerServer $newMaster){
		$this->newMaster = $newMaster;
	}

	public function getNewMaster() : PeerServer{
		return $this->newMaster;
	}
}
