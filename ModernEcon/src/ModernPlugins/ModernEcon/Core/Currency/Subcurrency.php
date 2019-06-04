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

final class Subcurrency{
	/** @var int */
	private $id;
	/** @var string */
	private $name;
	/** @var Currency */
	private $currency;
	/** @var string */
	private $symbolBefore;
	/** @var string */
	private $symbolAfter;
	/** @var int */
	private $magnitude;

	/**
	 * Subcurrency constructor.
	 *
	 * @param int      $id
	 * @param string   $name
	 * @param Currency $currency
	 * @param string   $symbolBefore
	 * @param string   $symbolAfter
	 * @param int      $magnitude
	 *
	 * @internal Only to be constructed directly from classes in this namespace.
	 *
	 */
	public function __construct(int $id, string $name, Currency $currency, string $symbolBefore, string $symbolAfter, int $magnitude){
		$this->id = $id;
		$this->name = $name;
		$this->currency = $currency;
		$this->symbolBefore = $symbolBefore;
		$this->symbolAfter = $symbolAfter;
		$this->magnitude = $magnitude;
	}

	public function getId() : int{
		return $this->id;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getCurrency() : Currency{
		return $this->currency;
	}

	public function getSymbolBefore() : string{
		return $this->symbolBefore;
	}

	public function getSymbolAfter() : string{
		return $this->symbolAfter;
	}

	public function getMagnitude() : int{
		return $this->magnitude;
	}

	public function toString(int $amount) : string{
		return $this->symbolBefore . $amount . $this->symbolAfter;
	}
}
