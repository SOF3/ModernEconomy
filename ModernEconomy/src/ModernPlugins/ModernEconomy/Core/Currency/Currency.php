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

use function floor;
use function implode;
use function uasort;

final class Currency{
	/** @var int */
	private $id;
	/** @var string */
	private $name;
	/** @var Subcurrency[] */
	private $subcurrencies = [];

	/**
	 * Currency constructor.
	 *
	 * @param int    $id
	 * @param string $name
	 *
	 * @internal Only to be called from classes in this namespace.
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
		uasort($subcurrencies, static function(Subcurrency $a, Subcurrency $b) : int{
			return -($a->getMagnitude() <=> $b->getMagnitude());
		});
		$this->subcurrencies = $subcurrencies;
	}

	public function toString(int $amount) : string{
		$output = [];
		foreach($this->subcurrencies as $subcurrency){
			if($subcurrency->getMagnitude() <= $amount){
				$output[] = $subcurrency->toString((int) floor($amount / $subcurrency->getMagnitude()));
				$amount %= $subcurrency->getMagnitude();
			}
		}
		return implode(" ", $output);
	}
}
