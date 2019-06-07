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

final class Account{
	/** @var AccountProvider */
	private $accountProvider;

	/** @var int */
	private $id;
	/** @var string */
	private $ownerType;
	/** @var string */
	private $ownerName;
	/** @var string */
	private $accountType;
	/** @var int */
	private $currencyId;
	/** @var Currency */
	private $currency;
	/** @var int */
	private $balance;
	/** @var int */
	private $lastAccessBeforeSelect;

	/**
	 * Account constructor.
	 *
	 * @param AccountProvider $accountProvider
	 * @param int             $id
	 * @param string          $ownerType
	 * @param string          $ownerName
	 * @param string          $accountType
	 * @param int             $currencyId
	 * @param int             $balance
	 * @param int             $lastAccessBeforeSelect
	 *
	 * @internal Only to be called from this module
	 */
	public function __construct(
		AccountProvider $accountProvider, int $id, string $ownerType, string $ownerName,
		string $accountType, int $currencyId, int $balance, int $lastAccessBeforeSelect){
		$this->accountProvider = $accountProvider;
		$this->id = $id;
		$this->ownerType = $ownerType;
		$this->ownerName = $ownerName;
		$this->accountType = $accountType;
		$this->currencyId = $currencyId;
		$this->balance = $balance;
		$this->lastAccessBeforeSelect = $lastAccessBeforeSelect;
	}

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
		yield from $this->accountProvider->getDb()->executeChange(Queries::CORE_ACCOUNT_SET_OWNER, [
			"id" => $this->id,
			"ownerType" => $type,
			"ownerName" => $name,
		]);
		$this->ownerType = $type;
		$this->ownerName = $name;
	}

	public function getAccountType() : string{
		return $this->accountType;
	}

	public function getCurrency() : Generator{
		if($this->currency === null){
			$this->currency = yield from $this->accountProvider->getCurrencyProvider()->getCurrency($this->currencyId);
		}
		return $this->currency;
	}

	public function getBalance() : int{
		return $this->balance;
	}

	public function getLastAccessBeforeSelect() : int{
		return $this->lastAccessBeforeSelect;
	}
}
