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

namespace ModernPlugins\ModernEcon;

use ModernPlugins\ModernEcon\Core\CoreModule;
use ModernPlugins\ModernEcon\Utils\AwaitDataConnector;
use pocketmine\plugin\PluginBase;
use poggit\libasynql\libasynql;
use PrefixedLogger;
use function array_map;
use function bin2hex;
use function random_bytes;

final class Main extends PluginBase{
	/** @var string */
	private $tempServerId;

	/** @var CoreModule */
	private $coreModule;

	public function onEnable() : void{
		$sqlFiles = [
			"core",
		];
		$sqlMap = ["mysql" => array_map(static function(string $file){
			return "$file.mysql.sql";
		}, $sqlFiles), "sqlite" => array_map(static function(string $file){
			return "$file.sqlite.sql";
		}, $sqlFiles)];
		$db = libasynql::create($this, $this->getConfig()->get("database"), $sqlMap);
		$db = new AwaitDataConnector($db);

		$this->tempServerId = bin2hex(random_bytes(8));
		$this->coreModule = new CoreModule(
			$this, new PrefixedLogger($this->getLogger(), "Core"), $db,
			$this->tempServerId);
	}

	public function getCoreModule() : CoreModule{
		return $this->coreModule;
	}
}
