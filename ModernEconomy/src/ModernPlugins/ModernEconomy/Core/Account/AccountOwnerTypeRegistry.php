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

namespace ModernPlugins\ModernEconomy\Core\Account;

final class AccountOwnerTypeRegistry{
	/** @var AccountOwnerType[] */
	private $registry = [];

	public function register(AccountOwnerType $type) : void{
		$this->registry[$type->getId()] = $type;
	}

	public function getById(string $id) : ?AccountOwnerType{
		return $this->registry[$id] ?? null;
	}

	/**
	 * @return AccountOwnerType[]
	 */
	public function getAll() : array{
		return $this->registry;
	}
}
