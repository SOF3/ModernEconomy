-- #!mysql
-- #{ core.account
-- #    { create
CREATE TABLE modernecon_account (
	id          INT PRIMARY KEY AUTO_INCREMENT,
	ownerType   VARCHAR(255) NOT NULL,
	ownerName   VARCHAR(255) NOT NULL,
	accountType VARCHAR(255) NOT NULL,
	currency    INT,
	balance     INT          NOT NULL,
	lastAccess  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
-- #    { set
-- #        { owner
-- #            :id int
-- #            :ownerType string
-- #            :ownerName string
UPDATE modernecon_account SET ownerType = :ownerType, ownerName = :ownerName WHERE id = :id;
-- #        }
-- #    }
-- #    { try-add
-- #        { if-min
-- #            * Add :amount to the account #:id if the current (old) balance is at least :ifMin
-- #            :id int
-- #            :amount int
-- #            :ifMin int
UPDATE modernecon_account
SET balance = balance + :amount
WHERE id = :id AND balance >= :ifMin;
-- #        }
-- #        { if-max
-- #            * Add :amount to the account #:id if the current (old) balance is at most :ifMax
-- #            :id int
-- #            :amount int
-- #            :ifMax int
UPDATE modernecon_account
SET balance = balance + :amount
WHERE id = :id AND balance <= :ifMax;
-- #        }
-- #    }
-- #}
