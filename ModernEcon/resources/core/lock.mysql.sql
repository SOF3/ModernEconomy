-- #!mysql
-- #{ core.lock
-- #    { create
-- #        * The modernecon_lock table contains a single row that contains the ID of the server that currently acquires the lock.
CREATE TABLE IF NOT EXISTS modernecon_lock (
	master       CHAR(16),
	majorVersion INT  NOT NULL,
	minorVersion INT  NOT NULL,
	config       TEXT NOT NULL,
	lastUpdate   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- #    }
-- #    { init
-- #        * Initializes the table to contain exactly one null row with a safe value.
INSERT INTO modernecon_lock (master, config, lastUpdate)
VALUES ('', '', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY));
-- #    }
-- #    { acquire
-- #        :serverId string
-- #        :majorVersion int
-- #        :minorVersion int
-- #        * Acquires the master status if previous master was down.
-- #        * Only call from non-master servers.
-- #        * `affectedRows == 1` indicates whether the master status is acquired.
-- #        * If two non-master servers execute this query simultaneously, the first one wins.
-- #        * This is under the assumption that all queries take way less than 10 seconds.
-- #        * The configuration is not updated. All servers shall continue to use the old configuration.
UPDATE modernecon_lock
SET master       = :serverId,
    majorVersion = :majorVersion,
    minorVersion = :minorVersion,
    lastUpdate  = CURRENT_TIMESTAMP
WHERE DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MINUTE) > lastUpdate;
-- atomically set the master as self if last update from previous master was more than one minute ago
-- #    }
-- #    { acquire-with-config
-- #        :serverId string
-- #        :majorVersion int
-- #        :minorVersion int
-- #        :config string
-- #        * Attempts to acquire the master status.
-- #        * Only call this method when the server just started.
-- #        * Successful acquisition will also modify the config.
UPDATE modernecon_lock
SET master       = :serverId,
    majorVersion = :majorVersion,
    minorVersion = :minorVersion,
    config       = :config,
    lastUpdate  = CURRENT_TIMESTAMP
WHERE DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MINUTE) > lastUpdate;
-- #    }
-- #    { release
-- #        :serverId string
-- #        * Releases the master status explicitly.
-- #        * Should be executed by the master server before its shutdown.
UPDATE modernecon_lock
SET lastUpdate = DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)
WHERE master = :serverId;
-- #    }
-- #    { maintain
-- #        :serverId string
-- #        * Maintains the master status.
-- #        * Only call from the master server.
-- #        * Call in intervals at least 5 seconds apart (recommended: every 30 seconds)
-- #        * `affectedRows == 1` indicates that the master status is maintained.
-- #        * `affectedRows == 0` implies that, for some reason, the master server changed.
UPDATE modernecon_lock
SET lastUpdate = CURRENT_TIMESTAMP
WHERE master = :serverId;
-- #    }
-- #    { query
-- #        * Checks the current master server.
-- #        * The result contains 1 row if there is an active master server, or 0 row if no active master.
-- #        * Do not rely on this query result for atomic operations.
-- #        * This is under the assumption that all queries take way less than 10 seconds.
-- #        * The config is not directly fetched, while the md5 hash of the config is fetched
SELECT master, majorVersion, minorVersion, MD5(config) config_hash
FROM modernecon_lock
WHERE DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 70 SECOND) < lastUpdate;
-- #    }
-- #    { query-config
-- #        * Downloads the latest configuration.
SELECT config, MD5(config) config_hash
FROM modernecon_lock
WHERE DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 70 SECOND) < lastUpdate;
-- #    }
-- #}
