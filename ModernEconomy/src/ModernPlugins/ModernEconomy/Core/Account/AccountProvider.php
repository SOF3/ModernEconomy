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
use ModernPlugins\ModernEconomy\Core\Currency\CurrencyProvider;
use ModernPlugins\ModernEconomy\DataBaseMigration;
use ModernPlugins\ModernEconomy\Generated\Queries;
use ModernPlugins\ModernEconomy\Utils\DataBase;
use function array_map;

final class AccountProvider{
	/** @var DataBase */
	private $db;
	/** @var CurrencyProvider */
	private $currencyProvider;

	public static function create(DataBase $db, CurrencyProvider $currencyProvider, ?DataBaseMigration $migration) : Generator{
		if($migration !== null){
			if($migration->getFromVersion() <= DataBaseMigration::EMPTY_MIGRATE_VERSION){
				yield from $db->executeGeneric(Queries::CORE_ACCOUNT_CREATE);
			}
		}

		$provider = new self;
		$provider->db = $db;
		$provider->currencyProvider = $currencyProvider;
		return $provider;
	}

	public function getAccount(int $id) : Generator{
		$row = yield from $this->db->executeSingleSelect(Queries::CORE_ACCOUNT_GET, [
			"id" => $id,
		]);

		return $this->fromRow($row);
	}

	public function listOwnedType(AccountOwnerType $ownerType, string $ownerName, AccountType $accountType) : Generator{
		$rows = yield from $this->db->executeSelect(Queries::CORE_ACCOUNT_LIST_BY_OWNED_TYPE, [
			"ownerType" => $ownerType->getId(),
			"ownerName" => $ownerName,
			"accountType" => $accountType->getId(),
		]);
		return array_map(function(array $row) use ($accountType, $ownerName, $ownerType) : Account{
			return $this->fromRow($row + [
					"ownerType" => $ownerType->getId(),
					"ownerName" => $ownerName,
					"accountType" => $accountType->getId(),
				]);
		}, $rows);
	}

	private function fromRow(array $row) : Account{
		return new Account($this, $row["id"], $row["ownerType"], $row["ownerName"],
			$row["accountType"], $row["currency"], $row["balance"], $row["lastAccess"]);
	}

	public function getDb() : DataBase{
		return $this->db;
	}

	public function getCurrencyProvider() : CurrencyProvider{
		return $this->currencyProvider;
	}

	public function createAccount(AccountOwnerType $ownerType, string $ownerName, AccountType $accountType, Currency $currency) : Generator{
		$id = yield $this->db->executeInsert(Queries::CORE_ACCOUNT_NEW, [
			"ownerType" => $ownerType->getId(),
			"ownerNae" => $ownerName,
			"accountType" => $accountType->getId(),
			"currency" => $currency->getId(),
			"balance" => 0.0,
		]);
		return $this->getAccount($id);
	}
}
