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
use ModernPlugins\ModernEconomy\Generated\Queries;
use ModernPlugins\ModernEconomy\Utils\DataBase;

final class AccountProvider{
	/** @var DataBase */
	private $db;
	/** @var CurrencyProvider */
	private $currencyProvider;

	public static function create(DataBase $db, CurrencyProvider $currencyProvider, bool $creating) : Generator{
		if($creating){
			yield $db->executeGeneric(Queries::CORE_ACCOUNT_CREATE);
		}

		$provider = new self;
		$provider->db = $db;
		$provider->currencyProvider = $currencyProvider;
		return $provider;
	}

	public function getAccount(int $id) : Generator{
		$row = yield $this->db->executeSelect(Queries::CORE_ACCOUNT_GET, [
			"id" => $id,
		]);

		return new Account($this, $row["id"], $row["ownerType"], $row["ownerName"],
			$row["accountType"], $row["currency"], $row["balance"], $row["lastAccess"]);
	}

	public function getDb() : DataBase{
		return $this->db;
	}

	public function getCurrencyProvider() : CurrencyProvider{
		return $this->currencyProvider;
	}
}
