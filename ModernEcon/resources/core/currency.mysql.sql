-- #!mysql
-- #{ core.currency
-- #    { create
-- #        { currency
-- #            * Creates the currency table
CREATE TABLE modernecon_currency (
	id   INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) UNIQUE
);
-- #        }
-- #        { subcurrency
-- #            * Creates the subcurrency table
CREATE TABLE modernecon_subcurrency (
	id           INT PRIMARY KEY AUTO_INCREMENT,
	name         VARCHAR(255),
	currency     INT,
	symbolBefore VARCHAR(255),
	symbolAfter  VARCHAR(255),
	magnitude    INT,
	UNIQUE KEY (currency, name),
	UNIQUE KEY (currency, magnitude),
	FOREIGN KEY (currency) REFERENCES modernecon_currency(id)
)
	CHARACTER SET 'utf8mb4';
-- #        }
-- #    }
-- #    { load-all
-- #        { currency
SELECT id, name
FROM modernecon_currency;
-- #        }
-- #        { subcurrency
SELECT id, name, currency, symbolBefore, symbolAfter, magnitude
FROM modernecon_subcurrency;
-- #        }
-- #    }
-- #    { load
-- #        { currency
-- #            :id int
SELECT name
FROM modernecon_currency
WHERE id = :id;
-- #        }
-- #        { subcurrency
-- #            :id int
SELECT id, name, currency, symbolBefore, symbolAfter, magnitude
FROM modernecon_subcurrency
WHERE currency = :id;
-- #        }
-- #    }
-- #    { get-id-by-name
-- #        :name string
SELECT id
FROM modernecon_currency
WHERE name = :name;
-- #    }
-- #    { add
-- #        { currency
-- #            :name string
-- #            * Adds a new currency
INSERT INTO modernecon_currency (name)
VALUES (:name);
-- #        }
-- #        { subcurrency
-- #            :name string
-- #            :currency int
-- #            :symbolBefore string
-- #            :symbolAfter string
-- #            :magnitude int
-- #            * Adds a new subcurrency
INSERT INTO modernecon_subcurrency (name, currency, symbolBefore, symbolAfter, magnitude)
VALUES (:name, :currency, :symbolBefore, :symbolAfter, :magnitude);
-- #        }
-- #    }
-- #}
