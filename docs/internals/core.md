---
title: Core Module
---

## Master acquisition
In a network of servers running ModernEconomy, there needs to be a "master" server that determines any race conditions
and perform run-once operations.
For example, certain database jobs are executed periodically;
running them on every server periodically would waste CPU.
In addition, all settings (except server-specific settings and database settings) are synchronized
through copying the configuration from the master server.

Master server status is managed through the `core.lock.*` queries,
as seen in the \Core\MasterManager class.
`Master***Event`s are dispatched when the master status changes.

## Currency
A currency is a unit for money.
There is no 1-to-1 mapping between different currencies.
Exchanging money between two currencies requires an `OperationType::EXCHANGE` operation.
All representations of money must contain a currency.

Each currency may contain one or more subcurrencies.
A subcurrency is a magnitude unit of a currency.
Therefore, different subcurrencies of the same currency are freely interchangeable.
For example, in real life, one US dollar and one British pound are different currencies,
so exchange between the two involves some cost,
while one British pound and 100 British pence are identical and can be freely interchanged.

The smallest subcurrency of each currency is used as the database storage unit.
In addition, all other subcurrencies must possess integer multiple values of this base subcurrency
to facilitate integer storage in databases without `DECIMAL` type support.

Currencies do not need to represent monetary ideas.
It is possible to create a currency that represents a piece of land or a diamond.
It is even possible for a currency to imply a "gift code" that can be redeemed into some different currencies.

## Account
An account contains some proportion of capital in the economy.
As a result, the sum of all account balances represent all capital accessible by the economy.

In general, economic activities should not involve a net increase or decrease in total capital.

An account may contain negative balance if it represents that a loss of money that,
for some reason, is not applied into other accounts yet.
An example is debt accounts.

Creation of accounts does not cause any change in the economy.
They should start at zero balance.

Accounts that have not been accessed for a long time may be cleaned up.
The decay period for different account types may be distinct.

Accounts are identified by accountType strings.
They are used to store the feature creating the account, but not specific data; that should be stored elsewhere.
accountType strings should only be used for efficiently filtering accounts, for analytics, etc.

Accounts are owned by an account owner.
Account owner may be any abstract concept that capital can be owned by,
such as players, teams, the server, etc.
(Creative ideas needed!)
Since each type of account owner may have multiple instances, the instances are distinguished by names.
The tuple `(ownerType, ownerName)` shall represent an instance of an account owner.

## Operation
An operation is the change of balance of one or multiple accounts.

There are 4 types of operations:

### Creation Operation
Capital is created out of thin air. Possible usages include:
- Admin gives money to a player
- Kill rewards.
  - This is possible on a PvP + Shop server.
    However, this is not recommended on an economy server,
    because this implies uncontrolled capital creation,
    potentially vulnerable to kill farming.
    On an economy server, it is more advisable to make this a transaction from the victim to the killer.
- Player joins with default capital
- A currency "coin" is purchased by the player using real-world payment methods.
  It cannot be obtained without real-world payment, so all creations of coins is a raw input.
- A plugin synchronizes each mined diamond with an account to represent the capital, so mining each diamond creates some value
  - This is a creation, not a transaction, if we do not consider the environment a part of the economy.
    Although wealth is transferred from the environment to the miner, the environment is beyond the economy,
    so there is a net gain of capital for the whole economy.

### Destruction Operation
Capital vanishes and cannot be tracked anymore. Possible usages include:
- Admin removes money from a player
- Deleting dead accounts.
  - This has relatively small effect on the economy since dead accounts are virtually isolated from the active economy anyway.

### Transaction Operation
The same amount of capital is transferred from one account to another.
The total capital in the economy is unchanged.
This is the type of operation for usual trading.

### Exchange operation
A decrease in one currency and an increase in another currency.
This is essentially a transaction, but since currencies are not directly transferrable to one another,
it is a different type of operation.
In particular, exchange might be a semantically irreversible operation,
e.g. transferring from currency A to B, then back to A, may result in a lower or higher amount of A,
subject to exchange semantics.
Possible usages include:
- Server item shops
  - Selling cobbles to a server shop causes the cobbles to be lost forever, with a player gain from nowhere.
    The capital was originally created from mining, which is a controlled operation.
    The exchange operation converts it from a item currency to a monetary currency.
- Buying monetary currency using "coin"s as defined in the Creation section
  - The capital was created from buying coins, which is a controlled operation.
    The exchange operation converts it from coins to a monetary currency.
- If a currency represents a gift code, redeeming the gift code into other forms of possessions is an exchange.


