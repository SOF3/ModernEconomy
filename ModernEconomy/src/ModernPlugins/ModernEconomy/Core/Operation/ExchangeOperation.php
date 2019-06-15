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

final class ExchangeOperation extends Operation{
	/** @var LazyAccount */
	private $source;
	/** @var int */
	private $sourceDecrease;
	/** @var LazyAccount */
	private $target;
	/** @var int */
	private $targetIncrease;

	public function __construct(DataBase $db, AccountProvider $accountProvider, int $id, int $time, string $type, int $sourceId, int $sourceDecrease, int $targetId, int $targetIncrease){
		parent::__construct($db, $accountProvider, $id, $time, $type);
		$this->source = new LazyAccount($accountProvider, $sourceId);
		$this->sourceDecrease = $sourceDecrease;
		$this->target = new LazyAccount($accountProvider,$targetId);
		$this->targetIncrease = $targetIncrease;
	}

	public function getSource() : Generator{
		return yield from $this->source->getInstance();
	}

	public function getSourceDecrease() : int{
		return $this->sourceDecrease;
	}

	public function getTarget() : Generator{
		return yield from $this->target->getInstance();
	}

	public function getTargetIncrease() : int{
		return $this->targetIncrease;
	}
}
