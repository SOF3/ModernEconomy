-- #!mysql
-- #{ core.version
-- #    { create
-- #        * The modernecon_version table is only to be used by the master server after its acquisition of master status
-- #        * This table assists the handover of master status along with plugin updates.
CREATE TABLE IF NOT EXISTS modernecon_version (
    pk       BOOL PRIMARY KEY,
    version  INT,
    updating BOOL
);
-- #    }
-- #    { init
-- #        * Populates the table with an initial value.
INSERT INTO modernecon_version (pk, version, updating)
VALUES (1, -1, 0)
ON DUPLICATE KEY UPDATE pk=1;
-- #    }
-- #    { query
-- #        * Checks the current version of the database.
-- #        * updating = 1 implies that the previous upgrade crashed in the middle.
SELECT version, updating
FROM modernecon_version;
-- #    }
-- #    { start-update
-- #        :version int
-- #        * Tries to acquire the update lock.
-- #        * affected_rows = 0 implies that the update lock has been acquired by something else.
UPDATE modernecon_version
SET version = :version, updating = 1
WHERE updating = 0;
-- #    }
-- #    { end-update
-- #        * Releases the update lock.
UPDATE modernecon_version
SET updating = 0
WHERE updating = 1;
-- #    }
-- #}
