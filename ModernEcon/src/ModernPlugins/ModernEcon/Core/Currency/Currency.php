<?php

/*
 * ModernEcon
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

namespace ModernPlugins\ModernEcon\Core\Currency;

use Generator;
use ModernPlugins\ModernEcon\Generated\Queries;
use ModernPlugins\ModernEcon\Utils\AwaitDataConnector;

final class Currency{
	/** @var int */
	private $id;
	/** @var string */
	private $name;
	/** @var Subcurrency[] */
	private $subcurrencies = [];

	public static function loadAll(AwaitDataConnector $db) : Generator{
		$currencies = yield $db->executeSelect(Queries::MODERNECON_CORE_CURRENCY_LOAD_ALL_CURRENCY);
		$subcurrencies = yield $db->executeSelect(Queries::MODERNECON_CORE_CURRENCY_LOAD_ALL_SUBCURRENCY);

		$output = [];
		foreach($currencies as $currencyRow){
			$currency = new Currency($currencyRow["id"], $currencyRow["name"]);
			foreach($subcurrencies as $subcurrencyRow){
				if($subcurrencyRow["currency"] === $currency->id){
					$subcurrency = new Subcurrency($subcurrencyRow["id"], $subcurrencyRow["name"], $currency,
						$subcurrencyRow["symbolBefore"], $subcurrencyRow["symbolAfter"],
						$subcurrencyRow["magnitude"]);
					$currency->subcurrencies[$subcurrency->getId()] = $subcurrency;
				}
			}
			$output[] = $currency;
		}
		return $output;
	}

	/**
	 * Currency constructor.
	 *
	 * @param int    $id
	 * @param string $name
	 *
	 * @internal Only to be called from classes in this namespace.
	 *
	 */
	public function __construct(int $id, string $name){
		$this->id = $id;
		$this->name = $name;
	}

	public function getId() : int{
		return $this->id;
	}

	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return Subcurrency[]
	 */
	public function getSubcurrencies() : array{
		return $this->subcurrencies;
	}

	/**
	 * @param Subcurrency[] $subcurrencies
	 *
	 * @internal Only to be called from classes in this namespace
	 *
	 */
	public function setSubcurrencies(array $subcurrencies) : void{
		$this->subcurrencies = $subcurrencies;
	}
}
