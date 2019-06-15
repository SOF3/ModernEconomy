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

use Generator;
use ModernPlugins\ModernEconomy\Core\Account\LazyAccount;

trait CreationDestructionOperationTrait{
	/** @var LazyAccount */
	private $account;
	/** @var int */
	private $amount;

	protected function setAccountId(int $accountId) : void{
		$this->account = new LazyAccount($this->getAccountProvider(), $accountId);
	}

	public function getAccount() : Generator{
		return yield from $this->account->getInstance();
	}

	abstract protected function asOperation() : Operation;

	public function getAmount() : int{
		return $this->amount;
	}
}
