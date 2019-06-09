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

namespace ModernPlugins\ModernEconomy;

use Generator;
use InvalidStateException;
use ModernPlugins\ModernEconomy\Configuration\Configuration;
use ModernPlugins\ModernEconomy\Core\CoreModule;
use ModernPlugins\ModernEconomy\Generated\Queries;
use ModernPlugins\ModernEconomy\Master\MasterManager;
use ModernPlugins\ModernEconomy\Utils\DataBase;
use pocketmine\plugin\PluginBase;
use poggit\libasynql\libasynql;
use PrefixedLogger;
use SOFe\AwaitGenerator\Await;
use function array_map;
use function bin2hex;
use function random_bytes;

/** @noinspection PhpUnused */

final class Main extends PluginBase{
	public const SQL_FILES = [
		"core/lock",
		"core/version",
		"core/currency",
		"core/account",
		"core/operation",
	];

	/**
	 * This number is the major version for database compat checks during master acquisition
	 */
	public const MAJOR_VERSION = 0;
	/**
	 * This number is the minor version for database compat checks during master acquisition
	 */
	public const MINOR_VERSION = 1;

	/** @var string */
	private $tempServerId;

	/** @var DataBase */
	private $db;

	/** @var MasterManager */
	private $masterManager;
	/** @var CoreModule */
	private $coreModule;

	public function onEnable() : void{
		$this->saveDefaultConfig();
		$configuration = new Configuration();
		$configuration->import($this->getConfig());

		$this->tempServerId = bin2hex(random_bytes(8));
		$this->db = $this->createDb();

		Await::g2c($this->asyncEnable($configuration));

		$this->db->getConnector()->waitAll();
		Await::g2c($this->masterManager->executeLoop($this->getScheduler()));
	}

	private function createDb() : DataBase{
		$sqlMap = [
			"mysql" => array_map(static function(string $file){
				return "$file.mysql.sql";
			}, self::SQL_FILES),
//			"sqlite" => array_map(static function(string $file){
//				return "$file.sqlite.sql";
//			}, self::SQL_FILES)
		];
		$db = libasynql::create($this, $this->getConfig()->get("database"), $sqlMap, true);
		return new DataBase($db);
	}

	private function asyncEnable(Configuration $config) : Generator{
		$this->masterManager = new MasterManager($this->createLogger("Master"), $this->db, $this->tempServerId);
		$config = yield from $this->syncConfig($config);

		/** @var DataBaseMigration $migration */
		$migration = yield from DataBaseMigration::create($this->db, $this->createLogger("Migration"));

		$this->coreModule = yield from CoreModule::create($this, $this->createLogger("Core"),
			$this->db, $config, $this->masterManager, $migration);

		if($migration !== null){
			yield from $migration->complete();
		}

		$this->getLogger()->info("Startup completed."); // necessary message to let user know when to start other servers
	}

	private function syncConfig(Configuration $configuration) : Generator{
		yield from $this->masterManager->executeInit();
		yield from $this->masterManager->executeIteration($configuration);
		if($this->masterManager->isMaster()){
			return $configuration;
		}
		return yield from $this->masterManager->fetchMasterConfiguration();
	}

	private function createLogger(string $name) : PrefixedLogger{
		return new PrefixedLogger($this->getLogger(), $name);
	}

	protected function onDisable(){
		if($this->db !== null){
			Await::f2c(function(){
				if($this->coreModule !== null){
					$this->coreModule->shutdown();
				}
				if($this->masterManager !== null){
					yield $this->masterManager->shutdown();
				}
			});
			$this->db->getConnector()->waitAll();
			$this->db->getConnector()->close();
		}
	}

	public function getCoreModule() : CoreModule{
		return $this->coreModule;
	}
}
