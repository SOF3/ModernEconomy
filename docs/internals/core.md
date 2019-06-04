# Core Module
## Master acquisition
In a network of servers running ModernEcon, there needs to be a "master" server that determines any race conditions
and perform run-once operations.
For example, certain database jobs are executed periodically;
running them on every server periodically would waste CPU.
In addition, all settings (except server-specific settings and database settings) are synchronized
through copying the configuration from the master server.

Master server status is managed through the `modernecon.core.lock.*` queries,
as seen in the \Core\MasterManager class.
`Master***Event`s are dispatched when the master status changes.

## Currency
A currency is a unit for money.
There is no 1-to-1 mapping between different currencies.
Exchanging money between two currencies requires an `OPERATION_EXCHANGE` operation.
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

