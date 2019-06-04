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
	last_access TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	KEY (ownerType, ownerName),
	KEY (accountType),
	FOREIGN KEY (currency) REFERENCES modernecon_currency(id)
);
-- #    }
-- #}
