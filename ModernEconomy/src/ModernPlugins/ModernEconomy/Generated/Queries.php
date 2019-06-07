<?php

/*
 * Auto-generated by libasynql-def
 * Created from account.mysql.sql, currency.mysql.sql, lock.mysql.sql, operation.mysql.sql
 */

declare(strict_types=1);

namespace ModernPlugins\ModernEconomy\Generated;

final class Queries{
	/**
	 * <h4>Declared in:</h4>
	 * - resources/core/account.mysql.sql:16
	 */
	public const CORE_ACCOUNT_CREATE = "core.account.create";

	/**
	 * <h4>Declared in:</h4>
	 * - resources/core/account.mysql.sql:22
	 *
	 * <h3>Variables</h3>
	 * - <code>:id</code> int, required in account.mysql.sql
	 */
	public const CORE_ACCOUNT_GET = "core.account.get";

	/**
	 * <h4>Declared in:</h4>
	 * - resources/core/account.mysql.sql:29
	 *
	 * <h3>Variables</h3>
	 * - <code>:ownerName</code> string, required in account.mysql.sql
	 * - <code>:ownerType</code> string, required in account.mysql.sql
	 * - <code>:id</code> int, required in account.mysql.sql
	 */
	public const CORE_ACCOUNT_SET_OWNER = "core.account.set.owner";

	/**
	 * <i>(Description from resources/core/currency.mysql.sql)</i>
	 *
	 * Adds a new currency
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/currency.mysql.sql:65
	 *
	 * <h3>Variables</h3>
	 * - <code>:name</code> string, required in currency.mysql.sql
	 */
	public const CORE_CURRENCY_ADD_CURRENCY = "core.currency.add.currency";

	/**
	 * <i>(Description from resources/core/currency.mysql.sql)</i>
	 *
	 * Adds a new subcurrency
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/currency.mysql.sql:75
	 *
	 * <h3>Variables</h3>
	 * - <code>:symbolBefore</code> string, required in currency.mysql.sql
	 * - <code>:symbolAfter</code> string, required in currency.mysql.sql
	 * - <code>:magnitude</code> int, required in currency.mysql.sql
	 * - <code>:currency</code> int, required in currency.mysql.sql
	 * - <code>:name</code> string, required in currency.mysql.sql
	 */
	public const CORE_CURRENCY_ADD_SUBCURRENCY = "core.currency.add.subcurrency";

	/**
	 * <i>(Description from resources/core/currency.mysql.sql)</i>
	 *
	 * Creates the currency table
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/currency.mysql.sql:11
	 */
	public const CORE_CURRENCY_CREATE_CURRENCY = "core.currency.create.currency";

	/**
	 * <i>(Description from resources/core/currency.mysql.sql)</i>
	 *
	 * Creates the subcurrency table
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/currency.mysql.sql:27
	 */
	public const CORE_CURRENCY_CREATE_SUBCURRENCY = "core.currency.create.subcurrency";

	/**
	 * <h4>Declared in:</h4>
	 * - resources/core/currency.mysql.sql:58
	 *
	 * <h3>Variables</h3>
	 * - <code>:name</code> string, required in currency.mysql.sql
	 */
	public const CORE_CURRENCY_GET_ID_BY_NAME = "core.currency.get-id-by-name";

	/**
	 * <h4>Declared in:</h4>
	 * - resources/core/currency.mysql.sql:33
	 */
	public const CORE_CURRENCY_LOAD_ALL_CURRENCY = "core.currency.load-all.currency";

	/**
	 * <h4>Declared in:</h4>
	 * - resources/core/currency.mysql.sql:37
	 */
	public const CORE_CURRENCY_LOAD_ALL_SUBCURRENCY = "core.currency.load-all.subcurrency";

	/**
	 * <h4>Declared in:</h4>
	 * - resources/core/currency.mysql.sql:45
	 *
	 * <h3>Variables</h3>
	 * - <code>:id</code> int, required in currency.mysql.sql
	 */
	public const CORE_CURRENCY_LOAD_CURRENCY = "core.currency.load.currency";

	/**
	 * <h4>Declared in:</h4>
	 * - resources/core/currency.mysql.sql:51
	 *
	 * <h3>Variables</h3>
	 * - <code>:id</code> int, required in currency.mysql.sql
	 */
	public const CORE_CURRENCY_LOAD_SUBCURRENCY = "core.currency.load.subcurrency";

	/**
	 * <i>(Description from resources/core/lock.mysql.sql)</i>
	 *
	 * Acquires the master status if previous master was down.
	 * Only call from non-master servers.
	 * `affectedRows == 1` indicates whether the master status is acquired.
	 * If two non-master servers execute this query simultaneously, the first one wins.
	 * This is under the assumption that all queries take way less than 10 seconds.
	 * The configuration is not updated. All servers shall continue to use the old configuration.
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/lock.mysql.sql:37
	 *
	 * <h3>Variables</h3>
	 * - <code>:minorVersion</code> int, required in lock.mysql.sql
	 * - <code>:majorVersion</code> int, required in lock.mysql.sql
	 * - <code>:serverId</code> string, required in lock.mysql.sql
	 */
	public const CORE_LOCK_ACQUIRE = "core.lock.acquire";

	/**
	 * <i>(Description from resources/core/lock.mysql.sql)</i>
	 *
	 * Attempts to acquire the master status.
	 * Only call this method when the server just started.
	 * Successful acquisition will also modify the config.
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/lock.mysql.sql:53
	 *
	 * <h3>Variables</h3>
	 * - <code>:minorVersion</code> int, required in lock.mysql.sql
	 * - <code>:majorVersion</code> int, required in lock.mysql.sql
	 * - <code>:serverId</code> string, required in lock.mysql.sql
	 * - <code>:config</code> string, required in lock.mysql.sql
	 */
	public const CORE_LOCK_ACQUIRE_WITH_CONFIG = "core.lock.acquire-with-config";

	/**
	 * <i>(Description from resources/core/lock.mysql.sql)</i>
	 *
	 * The modernecon_lock table contains a single row that contains the ID of the server that currently acquires the lock.
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/lock.mysql.sql:13
	 */
	public const CORE_LOCK_CREATE = "core.lock.create";

	/**
	 * <i>(Description from resources/core/lock.mysql.sql)</i>
	 *
	 * Initializes the table to contain exactly one null row with a safe value.
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/lock.mysql.sql:19
	 */
	public const CORE_LOCK_INIT = "core.lock.init";

	/**
	 * <i>(Description from resources/core/lock.mysql.sql)</i>
	 *
	 * Maintains the master status.
	 * Only call from the master server.
	 * Call in intervals at least 5 seconds apart (recommended: every 30 seconds)
	 * `affectedRows == 1` indicates that the master status is maintained.
	 * `affectedRows == 0` implies that, for some reason, the master server changed.
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/lock.mysql.sql:72
	 *
	 * <h3>Variables</h3>
	 * - <code>:serverId</code> string, required in lock.mysql.sql
	 */
	public const CORE_LOCK_MAINTAIN = "core.lock.maintain";

	/**
	 * <i>(Description from resources/core/lock.mysql.sql)</i>
	 *
	 * Checks the current master server.
	 * The result contains 1 row if there is an active master server, or 0 row if no active master.
	 * Do not rely on this query result for atomic operations.
	 * This is under the assumption that all queries take way less than 10 seconds.
	 * The config is not directly fetched, while the md5 hash of the config is fetched
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/lock.mysql.sql:82
	 */
	public const CORE_LOCK_QUERY = "core.lock.query";

	/**
	 * <i>(Description from resources/core/lock.mysql.sql)</i>
	 *
	 * Downloads the latest configuration.
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/lock.mysql.sql:88
	 */
	public const CORE_LOCK_QUERY_CONFIG = "core.lock.query-config";

	/**
	 * <i>(Description from resources/core/lock.mysql.sql)</i>
	 *
	 * Releases the master status explicitly.
	 * Should be executed by the master server before its shutdown.
	 *
	 * <h4>Declared in:</h4>
	 * - resources/core/lock.mysql.sql:61
	 *
	 * <h3>Variables</h3>
	 * - <code>:serverId</code> string, required in lock.mysql.sql
	 */
	public const CORE_LOCK_RELEASE = "core.lock.release";

	/**
	 * <h4>Declared in:</h4>
	 * - resources/core/operation.mysql.sql:22
	 */
	public const CORE_OPERATION_CREATE_DETAIL = "core.operation.create.detail";

	/**
	 * <h4>Declared in:</h4>
	 * - resources/core/operation.mysql.sql:12
	 */
	public const CORE_OPERATION_CREATE_INDEX = "core.operation.create.index";

	/**
	 * <h4>Declared in:</h4>
	 * - resources/core/operation.mysql.sql:30
	 *
	 * <h3>Variables</h3>
	 * - <code>:id</code> int, required in operation.mysql.sql
	 */
	public const CORE_OPERATION_GET_MERGED = "core.operation.get-merged";

}
