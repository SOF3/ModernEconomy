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

namespace ModernPlugins\ModernEconomy\Core\Currency;

use Generator;

final class LazyCurrency{
	/** @var CurrencyProvider */
	private $currencyProvider;
	/** @var int */
	private $id;
	/** @var Currency|null */
	private $instance = null;

	public function __construct(CurrencyProvider $currencyProvider, int $id, ?Currency $instance){
		$this->currencyProvider = $currencyProvider;
		$this->id = $id;
		$this->instance = $instance;
	}

	public function getId() : int{
		return $this->id;
	}

	public function getInstance() : Generator{
		if($this->instance===null){
			$this->instance = yield from $this->currencyProvider->getCurrency($this->id);
		}
		return $this->instance;
	}
}