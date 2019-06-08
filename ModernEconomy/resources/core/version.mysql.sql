-- #!mysql
-- #{ core.version
-- #    { create
CREATE TABLE IF NOT EXISTS modernecon_version (
    pk       BOOL PRIMARY KEY,
    version  INT,
    updating BOOL
);
-- #    }
-- #    { init
INSERT INTO modernecon_version (pk, version, updating)
VALUES (1, -1, 0)
ON DUPLICATE KEY UPDATE pk=1;
-- #    }
-- #    { query
SELECT version, updating
FROM modernecon_version;
-- #    }
-- #    { start-update
-- #        :version int
UPDATE modernecon_version
SET version = :version, updating = 1
WHERE updating = 0;
-- #    }
-- #    { end-update
UPDATE modernecon_version
SET updating = 0
WHERE updating = 1;
-- #    }
-- #}
