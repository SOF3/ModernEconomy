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

namespace ModernPlugins\ModernEcon\Utils;

use Generator;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskScheduler;
use SOFe\AwaitGenerator\Await;

final class AwaitUtils{
	public const TICKS = 1;
	public const SECONDS = 20;
	public const MINUTES = self::SECONDS * 60;
	public const HOURS = self::MINUTES * 60;

	public static function sleep(TaskScheduler $scheduler, int $amount, int $unit = self::TICKS) : Generator{
		$callback = yield;
		$scheduler->scheduleDelayedTask(new ClosureTask(static function() use ($callback){
			$callback();
		}), $amount * $unit);
		yield Await::ONCE;
	}

	private function __construct(){
	}
}
