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

namespace ModernPlugins\ModernEconomy\Configuration;

use Logger;
use pocketmine\utils\Config;
use function gettype;
use function in_array;
use function is_array;
use function is_bool;
use function is_finite;
use function is_float;
use function is_int;
use function is_string;
use function sort;

final class Configuration{
	public const CONFIG_CLASSES = [
		self::class,
		PlayerConfiguration::class,
	];

	/** @var Config|null */
	private $config;
	/** @var Logger */
	private $logger;

	/** @var PlayerConfiguration */
	private $playerConfiguration;

	public static function create(Config $config, Logger $logger) : Configuration{
		$instance = new Configuration;
		$instance->config = $config;
		$instance->logger = $logger;
		$instance->playerConfiguration = PlayerConfiguration::create($instance);
		$instance->config = null;
		return $instance;
	}


	public function getPlayerConfiguration() : PlayerConfiguration{
		return $this->playerConfiguration;
	}


	/**
	 * Reads a raw config value
	 *
	 * @param string $key
	 *
	 * @return mixed
	 * @throws ConfigurationException if $key is missing
	 */
	private function read(string $key){
		$value = $this->config->getNested($key);
		if($value === null){
			throw new ConfigurationException("Required attribute \"$key\" is missing");
		}
		return $value;
	}

	/**
	 * Reads a raw boolean config value
	 *
	 * @param string $key
	 *
	 * @return bool
	 * @throws ConfigurationException if $key is missing or is not boolean
	 */
	public function readBool(string $key) : bool{
		return $this->toBool($this->read($key), $key);
	}

	/**
	 * Reads a raw int config value
	 *
	 * @param string $key
	 *
	 * @return int
	 * @throws ConfigurationException if $key is missing or is not integer
	 */
	public function readInt(string $key) : int{
		return $this->toInt($this->read($key), $key);
	}

	/**
	 * Reads a raw float config value
	 *
	 * @param string $key
	 *
	 * @return float
	 * @throws ConfigurationException if $key is missing or is not convertible to float
	 */
	public function readFloat(string $key) : float{
		return $this->toFloat($this->read($key), $key);
	}

	/**
	 * Reads a raw string config value
	 *
	 * @param string $key
	 *
	 * @return string
	 * @throws ConfigurationException if $key is missing or is not convertible to string
	 */
	public function readString(string $key) : string{
		return $this->toString($this->read($key), $key);
	}

	public function readIntList(string $key) : array{
		return $this->checkArray($this->read($key), "toInt", $key, true);
	}

	public function readIntSet(string $key) : array{
		return $this->checkArray($this->read($key), "toInt", $key, false);
	}

	public function readFloatList(string $key) : array{
		return $this->checkArray($this->read($key), "toFloat", $key, true);
	}

	public function readFloatSet(string $key) : array{
		return $this->checkArray($this->read($key), "toFloat", $key, false);
	}

	public function readStringList(string $key) : array{
		return $this->checkArray($this->read($key), "toString", $key, true);
	}

	public function readStringSet(string $key) : array{
		return $this->checkArray($this->read($key), "toString", $key, false);
	}

	private function toBool($value, string $key) : bool{
		if(!is_bool($value)){
			throw new ConfigurationException("\"$key\" should be true or false, but a " . gettype($value) . " is found");
		}
		return $value;
	}

	private function toInt($value, string $key) : int{
		if(!is_int($value)){
			throw new ConfigurationException("\"$key\" should be an integer (without quotes), got " . gettype($value));
		}
		return $value;
	}

	private function toFloat($value, string $key) : float{
		if(is_int($value)){
			return (float) $value;
		}
		if(!is_float($value)){
			throw new ConfigurationException("\"$key\" should be a number, got " . gettype($value));
		}
		if(!is_finite($value)){
			throw new ConfigurationException("Only finite and real values are allowed in \"$key\", got " . gettype($value));
		}
		return $value;
	}

	private function toString($value, string $key) : string{
		if(is_int($value)){
			return (string) $value;
		}
		if(is_float($value)){
			$alt = ((string) $value) . (($value === (int) $value) ? ".0" : "0");
			$this->logger->warning("Warning in shared.yml: \"$key\" should be text, but it is parsed as a number $value. We cannot distinguish if you typed $value or $alt. Please add \"quotes\" around the value to disambiguate (and to hide this warning).");
			return (string) $value;
		}
		if(is_bool($value)){
			$possible = $value ? "y|Y|yes|Yes|YES|true|True|TRUE|on|On|ON" : "n|N|no|No|NO|false|False|FALSE|off|Off|OFF";
			throw new ConfigurationException("\"$key\" should be text, but it is parsed as a boolean. We cannot distinguish if you typed $possible. Please add \"quotes\" around the value to disambiguate (and to hide this warning).");
		}
		if(!is_string($value)){
			throw new ConfigurationException("\$key\" should be text, but it is parsed as an " . gettype($value) . ". Please add \"quotes\" around the value if it is intended to be text.");
		}
		return $value;
	}

	private function checkArray($array, string $method, string $key, bool $isList) : array{
		if(!is_array($array)){
			throw new ConfigurationException("\"$key\" should be a list, but we found a " . gettype($array) . ". See https://modernplugins.github.io/ModernEconomy/users/yaml#list-set for YAML format help.");
		}
		if(!self::isLinearArray($array)){
			throw new ConfigurationException("\"$key\" should be a list, but we found a mapping. See https://modernplugins.github.io/ModernEconomy/users/yaml#list-set for YAML format help.");
		}

		foreach($array as &$value){
			$value = $this->{$method}($value);
		}
		unset($value);

		if(!$isList){
			$dup = self::findDuplicates($array);
			if($dup !== null){
				throw new ConfigurationException("\"$key\" should be a unique list, but item #$dup repeats a previous item");
			}

			sort($array);
		}

		return $array;
	}

	private static function isLinearArray(array $array) : bool{
		$i = 0;
		foreach($array as $key => $_){
			if($key !== $i){
				return false;
			}
			$i++;
		}
		return true;
	}

	private static function findDuplicates(array $array){
		$unique = [];
		foreach($array as $i => $value){
			if(in_array($value, $unique, true)){
				return $value;
			}
			$unique[] = $value;
		}
		return null;
	}

	private function __construct(){
	}
}
