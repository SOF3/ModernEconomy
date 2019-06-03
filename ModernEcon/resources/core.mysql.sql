-- #!mysql
-- #{ modernecon.core
-- #    { lock
-- #        { create
-- #            * The modernecon_lock table contains a single row that contains the ID of the server that currently acquires the lock.
CREATE TABLE IF NOT EXISTS modernecon_lock (
	master       CHAR(16),
	majorVersion INT,
	minorVersion INT,
	last_update  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- #        }
-- #        { init
-- #            * Initializes the table to contain exactly one null row with a safe value.
INSERT INTO modernecon_lock (master, last_update)
VALUES ('', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 HOUR));
-- #        }
-- #        { acquire
-- #            :serverId string
-- #            :majorVersion int
-- #            :minorVersion int
-- #            * Acquires the master status if previous master was down.
-- #            * Only call from non-master servers.
-- #            * `affectedRows == 1` indicates whether the master status is acquired.
-- #            * If two non-master servers execute this query simultaneously, the first one wins.
-- #            * This is under the assumption that all queries take way less than 10 seconds.
-- #            * TODO: config synchronization
UPDATE modernecon_lock
SET master       = :serverId,
    majorVersion = :majorVersion,
    minorVersion = :minorVersion,
    last_update  = CURRENT_TIMESTAMP
WHERE DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MINUTE) > last_update;
-- atomically set the master as self if last update from previous master was more than one minute ago
-- #        }
-- #        { maintain
-- #            :serverId string
-- #            * Maintains the master status.
-- #            * Only call from the master server.
-- #            * Call in intervals at least 5 seconds apart (recommended: every 30 seconds)
-- #            * `affectedRows == 1` indicates that the master status is maintained.
-- #            * `affectedRows == 0` implies that, for some reason, the master server changed.
UPDATE modernecon_lock
SET last_update = CURRENT_TIMESTAMP
WHERE master = :serverId;
-- #        }
-- #        { query
-- #            * Checks the current master server.
-- #            * The result contains 1 row if there is an active master server, or 0 row if no active master.
-- #            * Do not rely on this query result for atomic operations.
-- #            * This is under the assumption that all queries take way less than 10 seconds.
SELECT master, majorVersion, minorVersion
FROM modernecon_lock
WHERE DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 70 SECOND) < last_update;
-- #        }
-- #    }
-- #}
