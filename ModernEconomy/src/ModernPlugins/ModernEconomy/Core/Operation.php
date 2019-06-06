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

use ModernPlugins\ModernEconomy\Utils\DataBase;

abstract class Operation{
	/** @var DataBase */
	protected $db;
	/** @var AccountProvider */
	protected $accountProvider;
	/** @var int */
	private $id;
	/** @var float */
	private $time;
	/** @var string */
	private $type;

	protected function __construct(DataBase $db, AccountProvider $accountProvider, int $id, int $time, string $type){
		$this->db = $db;
		$this->accountProvider = $accountProvider;
		$this->id = $id;
		$this->time = $time;
		$this->type = $type;
	}

	public function getId() : int{
		return $this->id;
	}

	public function getTime() : int{
		return $this->time;
	}

	public function getType() : string{
		return $this->type;
	}

	public function getAccountProvider() : AccountProvider{
		return $this->accountProvider;
	}
}
