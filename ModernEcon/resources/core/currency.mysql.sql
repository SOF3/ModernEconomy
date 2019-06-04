-- #!mysql
-- #{ modernecon.core.currency
-- #    { create
-- #        { currency
-- #            * Creates the currency table
CREATE TABLE currency (
	id   INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) UNIQUE
);
-- #        }
-- #        { subcurrency
-- #            * Creates the subcurrency table
CREATE TABLE subcurrency (
	id           INT PRIMARY KEY AUTO_INCREMENT,
	name         VARCHAR(255),
	currency     INT,
	symbolBefore VARCHAR(255),
	symbolAfter  VARCHAR(255),
	magnitude    INT,
	UNIQUE KEY (currency, name),
	UNIQUE KEY (currency, magnitude),
	FOREIGN KEY (currency) REFERENCES currency(id)
)
	CHARACTER SET 'utf8mb4';
-- #        }
-- #    }
-- #    { load-all
-- #        { currency
SELECT id, name
FROM currency;
-- #        }
-- #        { subcurrency
SELECT id, name, currency, symbolBefore, symbolAfter, magnitude
FROM subcurrency;
-- #        }
-- #    }
-- #    { add
-- #        { currency
-- #            :name string
-- #            * Adds a new currency
INSERT INTO currency (name)
VALUES (:name);
-- #        }
-- #        { subcurrency
-- #            :name string
-- #            :currency int
-- #            :symbolBefore string
-- #            :symbolAfter string
-- #            :magnitude int
-- #            * Adds a new subcurrency
INSERT INTO subcurrency (name, currency, symbolBefore, symbolAfter, magnitude)
VALUES (:name, :currency, :symbolBefore, :symbolAfter, :magnitude);
-- #        }
-- #    }
-- #}
