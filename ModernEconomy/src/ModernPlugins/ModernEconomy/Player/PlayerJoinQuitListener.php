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

namespace ModernPlugins\ModernEconomy\Player;

use ModernPlugins\ModernEconomy\Configuration\Configuration;
use ModernPlugins\ModernEconomy\Core\Account\Account;
use ModernPlugins\ModernEconomy\Core\Account\AccountOwnerType;
use ModernPlugins\ModernEconomy\Core\Account\AccountProvider;
use ModernPlugins\ModernEconomy\Core\Account\AccountType;
use ModernPlugins\ModernEconomy\Core\Currency\Currency;
use ModernPlugins\ModernEconomy\Core\Operation\OperationProvider;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use SOFe\AwaitGenerator\Await;

final class PlayerJoinQuitListener implements Listener{
	/** @var Configuration */
	private $configuration;
	/** @var AccountProvider */
	private $accountProvider;
	/** @var AccountOwnerType */
	private $playerType;
	/** @var AccountType */
	private $cashType;
	/** @var Currency */
	private $currency; // TODO define
	private $operationProvider;

	public function __construct(Configuration $configuration, AccountProvider $accountProvider, OperationProvider $operationProvider, AccountOwnerType $accountOwnerType, AccountType $accountType){
		$this->configuration = $configuration;
		$this->accountProvider = $accountProvider;
		$this->operationProvider = $operationProvider;
		$this->playerType = $accountOwnerType;
		$this->cashType = $accountType;
	}

	/**
	 * @param PlayerLoginEvent $event
	 *
	 * @priority MONITOR
	 */
	public function e_login(PlayerLoginEvent $event) : void{
		Await::f2c(function() use ($event){
			/** @var Account[] $accounts */
			$accounts = yield $this->accountProvider->listOwnedType($this->playerType, $event->getPlayer()->getRawUniqueId(), $this->cashType);
			if(empty($accounts)){
				$account = yield $this->accountProvider->createAccount($this->playerType, $event->getPlayer()->getRawUniqueId(), $this->cashType, $this->currency);
				yield $this->operationProvider->addCreation($account, $this->configuration->getPlayerConfiguration()->getInitialBalance(), PlayerModule::INITIAL_BALANCE_CREATION);
			}
		});
	}
}
