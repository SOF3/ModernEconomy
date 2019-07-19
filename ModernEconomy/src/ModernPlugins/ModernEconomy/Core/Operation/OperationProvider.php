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

namespace ModernPlugins\ModernEconomy\Core\Operation;

use AssertionError;
use Generator;
use InvalidArgumentException;
use ModernPlugins\ModernEconomy\Core\Account\Account;
use ModernPlugins\ModernEconomy\Core\Account\AccountProvider;
use ModernPlugins\ModernEconomy\DataBaseMigration;
use ModernPlugins\ModernEconomy\Generated\Queries;
use ModernPlugins\ModernEconomy\Utils\DataBase;
use function assert;
use function count;

final class OperationProvider{
	/** @var DataBase */
	private $db;
	/** @var AccountProvider */
	private $accountProvider;

	public static function create(DataBase $db, AccountProvider $accountProvider, ?DataBaseMigration $migration) : Generator{
		if($migration !== null){
			if($migration->getFromVersion() <= DataBaseMigration::EMPTY_MIGRATE_VERSION){
				yield from $db->executeGeneric(Queries::CORE_OPERATION_CREATE_INDEX);
				yield from $db->executeGeneric(Queries::CORE_OPERATION_CREATE_DETAIL);
			}
		}
		$operationProvider = new OperationProvider();
		$operationProvider->db = $db;
		$operationProvider->accountProvider = $accountProvider;
		return $operationProvider;
	}

	public function getCreation(int $id) : Generator{
		$row = yield from $this->db->executeSingleSelect(Queries::CORE_OPERATION_GET_MERGED, [
			"id" => $id,
		]);
		if($row === null){
			return null;
		}

		if($row["class"] !== OperationClass::CREATION){
			throw new InvalidArgumentException("The operation is not a creation");
		}
		$operation = new CreationOperation($this->db, $this->accountProvider, $row["id"], $row["time"], $row["type"],
			$row["account"], $row["amount"]);
		return $operation;
	}

	public function getDestruction(int $id) : Generator{
		$row = yield from $this->db->executeSingleSelect(Queries::CORE_OPERATION_GET_MERGED, [
			"id" => $id,
		]);
		if($row === null){
			return null;
		}

		if($row["class"] !== OperationClass::DESTRUCTION){
			throw new InvalidArgumentException("The operation is not a destruction");
		}
		$operation = new DestructionOperation($this->db, $this->accountProvider, $row["id"], $row["time"], $row["type"],
			$row["account"], -$row["amount"]);
		return $operation;
	}

	public function getTransaction(int $id) : Generator{
		$rows = yield from $this->db->executeSelect(Queries::CORE_OPERATION_GET_MERGED, [
			"id" => $id,
		]);
		if($rows[0]["class"] !== OperationClass::TRANSACTION){
			throw new InvalidArgumentException("THe operation is not a transaction");
		}

		assert(count($rows) === 2);
		assert($rows[0]["diff"] + $rows[1]["diff"] === 0);

		if($rows[0]["diff"] > 0){
			[$source, $target] = [$rows[1], $rows[0]];
		}else{
			[$source, $target] = [$rows[0], $rows[1]];
		}

		return new TransactionOperation($this->db, $this->accountProvider,
			$id, $rows[0]["time"], $rows[0]["type"], $source["account"], $target["account"], $target["diff"]);
	}

	public function getExchange(int $id) : Generator{
		$rows = yield from $this->db->executeSelect(Queries::CORE_OPERATION_GET_MERGED, [
			"id" => $id,
		]);
		if($rows[0]["class"] !== OperationClass::EXCHANGE){
			throw new InvalidArgumentException("THe operation is not a transaction");
		}

		assert(count($rows) === 2);

		if($rows[0]["diff"] > 0 && $rows[1]["diff"] < 0){
			[$source, $target] = [$rows[1], $rows[0]];
		}elseif($rows[0]["diff"] < 0 && $rows[1]["diff"] > 0){
			[$source, $target] = [$rows[0], $rows[1]];
		}else{
			throw new AssertionError("Diff signs should be opposite");
		}

		return new ExchangeOperation($this->db, $this->accountProvider, $id, $rows[0]["time"], $rows[0]["type"],
			$source["account"], -$source["diff"], $target["account"], $target["diff"]);
	}

	public function addCreation(Account $account, int $amount, string $type) : Generator{
		// TODO implement
	}

	private function __construct(){

	}
}
