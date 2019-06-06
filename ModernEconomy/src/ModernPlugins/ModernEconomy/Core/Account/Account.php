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

namespace ModernPlugins\ModernEconomy\Core\Account;

use Generator;
use ModernPlugins\ModernEconomy\Core\Currency\Currency;
use ModernPlugins\ModernEconomy\Core\Currency\CurrencyManager;
use ModernPlugins\ModernEconomy\Generated\Queries;
use ModernPlugins\ModernEconomy\Utils\DataBase;

final class Account{
	/** @var DataBase */
	private $db;

	/** @var int */
	private $id;
	/** @var string */
	private $ownerType;
	/** @var string */
	private $ownerName;
	/** @var string */
	private $accountType;
	/** @var Currency */
	private $currency;
	/** @var int */
	private $balance;
	/** @var int */
	private $lastAccessBeforeSelect;

	public function getId() : int{
		return $this->id;
	}

	public function getOwnerType() : string{
		return $this->ownerType;
	}

	public function getOwnerName() : string{
		return $this->ownerName;
	}

	public function setOwnerType(string $type, string $name) : Generator{
		yield $this->db->executeChange(Queries::CORE_ACCOUNT_SET_OWNER, [
			"id" => $this->id,
			"ownerType" => $type,
			"ownerName" => $name,
		]);
	}

	public function getAccountType() : string{
		return $this->accountType;
	}

	public function getCurrency() : Currency{
		return $this->currency;
	}

	public function getBalance() : int{
		return $this->balance;
	}

	public function tryAddBalance(int $amount, int $max = 1 << 31) : Generator{
		$affectedRows = yield $this->db->executeChange(Queries::CORE_ACCOUNT_TRY_ADD_IF_MAX, [
			"id" => $this->id,
			"amount" => $amount,
			"ifMax" => $max - $amount,
		]);
		return $affectedRows > 0;
	}

	public function trySubtractBalance(int $amount, int $min = 0) : Generator{
		$affectedRows = yield $this->db->executeChange(Queries::CORE_ACCOUNT_TRY_ADD_IF_MIN, [
			"id" => $this->id,
			"amount" => -$amount,
			"ifMax" => $min + $amount,
		]);
		return $affectedRows > 0;
	}

	public static function getAccount(DataBase $db, CurrencyManager $currencyManager, int $id) : Generator{
		$row = yield $db->executeSelect(Queries::CORE_ACCOUNT_GET, [
			"id" => $id,
		]);

		$account = new Account;
		$account->db = $db;
		$account->id = $row["id"];
		$account->ownerType = $row["ownerType"];
		$account->ownerName = $row["ownerName"];
		$account->accountType = $row["accountType"];
		$account->currency = yield $currencyManager->getCurrency($row["currency"]);
		$account->balance = $row["balance"];
		$account->lastAccessBeforeSelect = $row["lastAccess"];
		return $account;
	}

	private function __construct(){
	}
}
