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
use Logger;
use ModernPlugins\ModernEconomy\Generated\Queries;
use ModernPlugins\ModernEconomy\Utils\DataBase;
use RuntimeException;
use function sleep;

final class DataBaseMigration{
	/**
	 * This number is the absolute database version number used to calculate required database structure changes
	 */
	public const MIGRATE_VERSION = 1;
	/**
	 * This number is the minimum database version number supported for migration.
	 */
	public const MIN_MIGRATE_VERSION = 0;

	public const EMPTY_MIGRATE_VERSION = -1;

	/** @var int */
	private $fromVersion;
	/** @var Logger */
	private $logger;
	/** @var DataBase */
	private $db;

	public static function create(DataBase $db, Logger $logger) : Generator{
		yield from $db->executeGeneric(Queries::CORE_VERSION_CREATE);
		yield from $db->executeGeneric(Queries::CORE_VERSION_INIT);

		$row = yield from $db->executeSingleSelect(Queries::CORE_VERSION_QUERY);
		if($row["updating"]){
			throw new RuntimeException("Cannot use database because last migration crashed. Please reset the database.");
		}

		$version = $row["version"];
		if($version > self::MIGRATE_VERSION){
			throw new RuntimeException("Cannot use database with an old version of ModernEconomy after migration to a newer version. Consider rolling back the database if you need to use the old version.");
		}

		if($version === self::MIGRATE_VERSION){
			return null;
		}

		if($version !== -1){
			if($version < self::MIN_MIGRATE_VERSION){
				throw new RuntimeException("The database is too old to migrate. You have to install an older version of ModernEconomy to migrate this database, or reset everything. See the user guide for version details.");
			}
			$logger->warning("Migrating database to a newer version. This may not be reversible. Consider creating a backup before migration.");
			$logger->warning("Migration will start in 10 seconds. Type Ctrl-C to stop migration and backup first.");
			sleep(10);
		}else{
			$logger->info("Initializing database for the first time.");
		}
		$changed = yield from $db->executeChange(Queries::CORE_VERSION_START_UPDATE, [
			"version" => self::MIGRATE_VERSION,
		]);
		if($changed === 0){
			throw new RuntimeException("Failed to acquire the lock for database migration.");
		}

		$migration = new DataBaseMigration;
		$migration->fromVersion = $version;
		$migration->logger = $logger;
		$migration->db = $db;
		return $migration;
	}

	private function __construct(){
	}

	public function getFromVersion() : int{
		return $this->fromVersion;
	}

	public function complete() : Generator{
		$this->logger->info("Database migration completed.");
		$changed = yield from $this->db->executeChange(Queries::CORE_VERSION_END_UPDATE);
		if($changed === 0){
			throw new InvalidStateException("Unexpected race condition. Potential database corruption?");
		}
	}
}
