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

use ModernPlugins\ModernEconomy\Utils\DataBase;

final class CreationOperation extends Operation{
	/** @var Account */
	private $account;

	protected function __construct(DataBase $db, int $id, int $time, string $type, Account $account, int $amount){
		parent::__construct($db, $id, $time, $type);
	}

	public function getAccount() : Account{
		return $this->account;
	}
}
