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
A `MasterAcquisitionEvent` is dispatched when the server gains master status.
