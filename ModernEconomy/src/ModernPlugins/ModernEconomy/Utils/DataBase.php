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

namespace ModernPlugins\ModernEconomy\Utils;

use Generator;
use poggit\libasynql\DataConnector;
use SOFe\AwaitGenerator\Await;

/**
 * await-generator style wrapper for libasynql DataConnector
 */
final class DataBase{
	/** @var DataConnector */
	private $connector;

	public function __construct(DataConnector $connector){
		$this->connector = $connector;
	}

	public function getConnector() : DataConnector{
		return $this->connector;
	}

	public function executeGeneric(string $queryName, array $args = []) : Generator{
		$this->connector->executeGeneric($queryName, $args, yield, yield Await::REJECT);
		return yield Await::ONCE;
	}

	public function executeChange(string $queryName, array $args = []) : Generator{
		$this->connector->executeChange($queryName, $args, yield, yield Await::REJECT);
		return yield Await::ONCE;
	}

	public function executeInsert(string $queryName, array $args = []) : Generator{
		$resolve = yield;
		$this->connector->executeInsert($queryName, $args, static function(int $insertId,
			/** @noinspection PhpUnusedParameterInspection */ int $affectedRows) use ($resolve): void{
			$resolve($insertId);
		}, yield Await::REJECT);
		return yield Await::ONCE;
	}

	public function executeInsertWithAffectedRows(string $queryName, array $args = []) : Generator{
		$resolve = yield;
		$this->connector->executeInsert($queryName, $args, static function(int $insertId, int $affectedRows) use ($resolve): void{
			$resolve([$insertId, $affectedRows]);
		}, yield Await::REJECT);
		return yield Await::ONCE;
	}

	public function executeSelect(string $queryName, array $args = []) : Generator{
		$this->connector->executeSelect($queryName, $args, yield, yield Await::REJECT);
		return yield Await::ONCE;
	}

	public function executeSingleSelect(string $queryName, array $args = []) : Generator{
		return (yield $this->executeSelect($queryName,$args))[0] ?? null;
	}
}
