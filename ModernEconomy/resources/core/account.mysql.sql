-- #!mysql
-- #{ core.account
-- #    { create
CREATE TABLE modernecon_account (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    ownerType   VARCHAR(255) NOT NULL,
    ownerName   VARCHAR(255) NOT NULL,
    accountType VARCHAR(255) NOT NULL,
    currency    INT          NOT NULL,
    balance     BIGINT       NOT NULL,
    lastAccess  TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    KEY (ownerType, ownerName),
    KEY (accountType),
    FOREIGN KEY (currency) REFERENCES modernecon_currency(id)
);
-- #    }
-- #    { get
-- #        :id int
SELECT id, ownerType, ownerName, accountType, currency, balance, UNIX_TIMESTAMP(lastAccess) lastAccess
FROM modernecon_account
WHERE id = :id;
-- #    }
-- #    { list
-- #        { by-owned-type
-- #            :ownerType string
-- #            :ownerName string
-- #            :accountType string
SELECT id, currency, balance, UNIX_TIMESTAMP(lastAccess)
FROM modernecon_account
WHERE ownerType = :ownerType AND ownerName = :ownerName AND accountType = :accountType;
-- #        }
-- #    }
-- #    { set
-- #        { owner
-- #            :id int
-- #            :ownerType string
-- #            :ownerName string
UPDATE modernecon_account SET ownerType = :ownerType, ownerName = :ownerName WHERE id = :id;
-- #        }
-- #    }
-- #    { new
-- #        :ownerType string
-- #        :ownerName string
-- #        :accountType string
-- #        :currency int
INSERT INTO modernecon_account (ownerType, ownerName, accountType, currency, balance, lastAccess)
VALUES (:ownerType, :ownerName, :accountType, :currency, 0, CURRENT_TIMESTAMP(3));
-- #    }
-- #}
