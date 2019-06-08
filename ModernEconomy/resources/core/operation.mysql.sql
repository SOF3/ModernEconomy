-- #!mysql
-- #{ core.operation
-- #    { create
-- #        { index
CREATE TABLE modernecon_operation_index (
    id    INT PRIMARY KEY AUTO_INCREMENT,
    time  TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    class SMALLINT     NOT NULL,
    type  VARCHAR(255) NOT NULL,
    INDEX (type, time)
);
-- #        }
-- #        { detail
CREATE TABLE modernecon_operation_detail (
    id      INT,
    account INT,
    diff    BIGINT NOT NULL,
    PRIMARY KEY (id, account),
    FOREIGN KEY (id) REFERENCES modernecon_operation_index(id) ON DELETE CASCADE,
    FOREIGN KEY (account) REFERENCES modernecon_account(id)
);
-- #        }
-- #    }
-- #    { get-merged
-- #        :id int
SELECT i.id, UNIX_TIMESTAMP(time) time, class, type, account, diff
FROM modernecon_operation_index i
         INNER JOIN modernecon_operation_detail d ON i.id = m.id
WHERE i.id = :id;
-- #    }
-- #}
