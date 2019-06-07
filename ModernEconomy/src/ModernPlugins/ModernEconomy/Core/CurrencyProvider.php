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
use InvalidStateException;
use ModernPlugins\ModernEconomy\Generated\Queries;
use ModernPlugins\ModernEconomy\Master\MasterManager;
use ModernPlugins\ModernEconomy\Utils\DataBase;

final class CurrencyProvider{
	/** @var DataBase */
	private $db;
	/** @var MasterManager */
	private $masterManager;

	public static function create(DataBase $db, MasterManager $masterManager, bool $creating) : Generator{
		if($creating){
			yield from $db->executeGeneric(Queries::CORE_CURRENCY_CREATE_CURRENCY);
			yield from $db->executeGeneric(Queries::CORE_CURRENCY_CREATE_SUBCURRENCY);
		}

		$provider = new self;
		$provider->db = $db;
		$provider->masterManager = $masterManager;
		return $provider;
	}

	public function getCurrencyByName(string $name) : Generator{
		$idRow = yield from $this->db->executeSingleSelect(Queries::CORE_CURRENCY_GET_ID_BY_NAME, [
			"name" => $name,
		]);
		if($idRow === null){
			return null;
		}
		return yield from $this->getCurrency($idRow["id"]);
	}

	public function getCurrency(int $id) : Generator{
		$currencyRow = yield from $this->db->executeSingleSelect(Queries::CORE_CURRENCY_LOAD_CURRENCY, [
			"id" => $id,
		]);
		if($currencyRow === null){
			return null;
		}
		$currency = new Currency($currencyRow["id"], $currencyRow["name"]);
		$subcurrencies = [];
		foreach(yield from $this->db->executeSelect(Queries::CORE_CURRENCY_LOAD_SUBCURRENCY, [
			"id" => $id,
		]) as $row){
			$subcurrency = new Subcurrency($row["id"], $row["name"], $currency,
				$row["symbolBefore"], $row["symbolAfter"],
				$row["magnitude"]);
			$subcurrencies[$subcurrency->getId()] = $subcurrency;
		}
		$currency->setSubcurrencies($subcurrencies);
		return $currency;
	}

	public function createCurrency(string $name, string $subName, string $symbolBefore, string $symbolAfter) : Generator{
		if(!$this->masterManager->isMaster()){
			throw new InvalidStateException("Currencies can only be created by the master server");
		}

		$id = yield from $this->db->executeInsert(Queries::CORE_CURRENCY_ADD_CURRENCY, [
			"name" => $name,
		]);
		$currency = new Currency($id, $name);

		yield from $this->createSubcurrency($currency, $subName, $symbolBefore, $symbolAfter, 1);

		return $currency;
	}

	public function createSubcurrency(Currency $currency, string $name, string $symbolBefore, string $symbolAfter, int $magnitude) : Generator{
		$id = yield from $this->db->executeInsert(Queries::CORE_CURRENCY_ADD_SUBCURRENCY, [
			"name" => $name,
			"currency" => $currency->getId(),
			"symbolBefore" => $symbolBefore,
			"symbolAfter" => $symbolAfter,
			"magnitude" => $magnitude,
		]);
		$subcurrency = new Subcurrency($id, $name, $currency, $symbolBefore, $symbolAfter, 1);
		$currency->setSubcurrencies($currency->getSubcurrencies() + [$id => $subcurrency]);
		return $subcurrency;
	}

	private function __construct(){
	}
}
