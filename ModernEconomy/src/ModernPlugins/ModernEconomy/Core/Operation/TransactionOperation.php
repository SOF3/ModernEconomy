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
use ModernPlugins\ModernEconomy\Core\Account\AccountProvider;
use ModernPlugins\ModernEconomy\Core\Account\LazyAccount;
use ModernPlugins\ModernEconomy\Utils\DataBase;

final class TransactionOperation extends Operation{
	/** @var LazyAccount */
	private $source;
	/** @var LazyAccount */
	private $target;
	/** @var int */
	private $amount;

	public function __construct(DataBase $db, AccountProvider $accountProvider, int $id, int $time, string $type, int $sourceId, int $targetId, int $amount){
		parent::__construct($db, $accountProvider, $id, $time, $type);
		$this->source = new LazyAccount($accountProvider, $sourceId);
		$this->target = new LazyAccount($accountProvider, $targetId);
		$this->amount = $amount;
	}

	public function getSource() : Generator{
		return yield from $this->source->getInstance();
	}

	public function getTarget() : Generator{
		return yield from $this->target->getInstance();
	}

	public function getAmount() : int{
		return $this->amount;
	}
}
