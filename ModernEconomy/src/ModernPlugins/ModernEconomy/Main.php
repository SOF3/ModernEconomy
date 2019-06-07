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
use RuntimeException;
use SOFe\AwaitGenerator\Await;
use function array_map;
use function bin2hex;
use function random_bytes;
use function sleep;

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
	/**
	 * This number is the absolute database version number used to calculate required database structure changes
	 */
	public const DB_VERSION = 1;
	/**
	 * This number is the minimum database version number supported for migration.
	 */
	public const MIN_MIGRATE_VERSION = 0;
	public const EMPTY_DB_VERSION = -1;

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
		$this->masterManager = new MasterManager(new PrefixedLogger($this->getLogger(), "Master"), $this->db, $this->tempServerId);
		$config = yield from $this->syncConfig($config);

		$version = yield from $this->checkDbVersion();

		$this->coreModule = yield from CoreModule::create(
			$this, new PrefixedLogger($this->getLogger(), "Core"),
			$this->db, $config, $this->masterManager, $version);

		if($version !== null){
			$this->getLogger()->info("Database migration completed.");
			$changed = yield from $this->db->executeChange(Queries::CORE_VERSION_END_UPDATE);
			if($changed === 0){
				throw new InvalidStateException("Unexpected race condition. Potential database corruption?");
			}
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

	private function checkDbVersion() : Generator{
		yield from $this->db->executeGeneric(Queries::CORE_VERSION_CREATE);
		yield from $this->db->executeGeneric(Queries::CORE_VERSION_INIT);

		$row = $this->db->executeSingleSelect(Queries::CORE_VERSION_QUERY);
		if($row["updating"]){
			throw new RuntimeException("Cannot use database because last migration crashed. Please reset the database.");
		}

		$version = $row["version"];
		if($version > self::DB_VERSION){
			throw new RuntimeException("Cannot use database with an old version of ModernEconomy after migration to a newer version. Consider rolling back the database if you need to use the old version.");
		}

		if($version === self::DB_VERSION){
			return null;
		}

		if($version !== -1){
			if($version < self::MIN_MIGRATE_VERSION){
				throw new RuntimeException("The database is too old to migrate. You have to install an older version of ModernEconomy to migrate this database, or reset everything. See the user guide for version details.");
			}
			$this->getLogger()->warning("Migrating database to a newer version. This may not be reversible. Consider creating a backup before migration.");
			$this->getLogger()->warning("Migration will start in 10 seconds. Type Ctrl-C to stop migration and backup first.");
			sleep(10);
		}
		$changed = $this->db->executeChange(Queries::CORE_VERSION_START_UPDATE, [
			"version" => self::DB_VERSION,
		]);
		if($changed === 0){
			throw new RuntimeException("Failed to acquire the lock for database migration.");
		}
		return $version;
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
