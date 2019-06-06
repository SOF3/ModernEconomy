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
use InvalidArgumentException;
use ModernPlugins\ModernEconomy\Generated\Queries;
use ModernPlugins\ModernEconomy\Utils\DataBase;

abstract class Operation{
	/** @var DataBase */
	private $db;
	/** @var int */
	private $id;
	/** @var float */
	private $time;
	/** @var string */
	private $type;

	public function getId() : int{
		return $this->id;
	}

	public function getTime() : int{
		return $this->time;
	}

	public function getType() : string{
		return $this->type;
	}

	public static function getCreation(DataBase $db, CurrencyManager $currencyManager, int $id) : Generator{
		$rows = yield $db->executeSelect(Queries::CORE_OPERATION_GET_CREATION_OR_DESTRUCTION, [
			"id" => $id,
		]);
		if(empty($rows)){
			return null;
		}
		$row = $rows[0];

		if($row["class"] !== OperationType::TYPE_CREATION){
			throw new InvalidArgumentException("The operation is not a creation");
		}
		$operation = new CreationOperation($db, $row["id"], $row["time"], $row["type"],
			yield Account::getAccount($db, $currencyManager, $row["account"]), $row["amount"]);
		return $operation;
	}

	protected function __construct(DataBase $db, int $id, int $time, string $type){
		$this->db = $db;
		$this->id = $id;
		$this->time = $time;
		$this->type = $type;
	}
}
