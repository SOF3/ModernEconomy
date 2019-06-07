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
use function assert;

trait CreationDestructionOperationTrait{
	/** @var int */
	private $accountId;
	/** @var Account */
	private $account = null;
	/** @var int */
	private $amount;

	public function getAccount() : Generator{
		if($this->account === null){
			$this->account = yield from $this->asOperation()->getAccountProvider()->getAccount($this->accountId);
		}
		return $this->account;
	}

	abstract protected function asOperation() : Operation;

	public function getAmount() : int{
		return $this->amount;
	}
}
