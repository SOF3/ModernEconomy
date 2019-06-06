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

class PeerServer{
	/** @var string */
	private $serverId;
	/** @var int */
	private $majorVersion;
	/** @var int */
	private $minorVersion;

	public function __construct(string $serverId, int $majorVersion, int $minorVersion){
		$this->serverId = $serverId;
		$this->majorVersion = $majorVersion;
		$this->minorVersion = $minorVersion;
	}

	public function getServerId() : string{
		return $this->serverId;
	}

	public function getMajorVersion() : int{
		return $this->majorVersion;
	}

	public function getMinorVersion() : int{
		return $this->minorVersion;
	}
}
