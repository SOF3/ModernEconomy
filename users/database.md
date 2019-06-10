---
title: Database
---

ModernEconomy supports two types of databases: SQLite3 database `sqlite` and MySQL database `mysql`.
You can customize the database settings with the database.yml of the server.

## SQLite3 database
SQLite3 is stored in a single file. By default, the file is in `plugin_data/ModernEconomy/data.sqlite`.
Change the `sqlite.file` value to change the file location, relative to `plugin_data/ModernEconomy/`.

Do not put a slash in front of the path, unless you want to type an absolute path in your filesystem (e.g. `/media/floppy/xialotecon.sqlite`).
For Windows users, paths starting with the drive name then a `:` are also resolved as absolute paths (e.g. `C:/Users/Public/xialotecon.sqlite`).
Backslashes (`\`) and forward slashes (`/`) are treated as the same on Windows.

Make sure `type` is set to `sqlite` and worker-limit` is set to `1` if you use SQLite3.

You must install the `sqlite3` extension in your PHP binaries to use SQLite3 databases.
* If you use the official PocketMine-MP installer:
  * For Windows users: look at the `bin/php/php.ini` file
  * For MacOS/Linux users: look at the `bin/php7/bin/php.ini` file
  * Look for the lines starting with `;extension=`.
    One of these lines should contain `sqlite3`, e.g. `;extension=sqlite3` or `;extension=php_sqlite3.dll` (but not `pdo_sqlite`).
    Delete the `;` in the beginning, then restart the server.
* If you use PHP binaries from other sources:
  * Usually you should have the `sqlite3` extension installed by default.
  * If you don't, and you can't figure it out yourself, use the official [PocketMine-MP installer][pmmp installer].

## MySQL database
MySQL is a database server that runs in a separate process independent of PocketMine.
Therefore, MySQL allows you to access the data from multiple places, e.g. from multiple PocketMine servers, from web servers, etc.
However, MySQL setup is a bit more complicated.

To use the MySQL database, you need to install a MySQL server.
Version 5.7 or above is required.

You also need a MySQL client to edit settings and allow ModernEconomy to use the database.

### Installation for Windows
TODO

### Installation for Linux
TODO

### Installation for Docker
TODO

### After installation
After installing MySQL server, connect to it using the client and run the following queries:

```sql
CREATE USER 'me_user'@'%' IDENTIFIED WITH 'mysql_native_password' BY 'your-password';
CREATE SCHEMA moderneconomy;
GRANT ALL ON moderneconomy.* TO 'me_user'@'%';
```

`me_user` is the MySQL user for ModernEconomy. `moderneconomy` is the MySQL schema for ModernEconomy.
In general, it is OK to share the same MySQL user and schema with other plugins if you already have one.
Replace `your-password` with the password for the account.

Also edit `database.yml` with your choice of user, password and schema.
Remember to set `type` to `mysql` and `worker-limit` to 2 if you use MySQL.
You may want to try increasing this number if ModernEconomy responds very slowly,
especially during server start when a lot of players join together.
(However this does not affect server TPS, nor does it reduce server lag in general)
(Using too many workers, e.g. 10, doesn't really make sense)

You may want to host the MySQL server on a different host (don't worry, this won't increase server lag).

  [pmmp installer]: http://pmmp.readthedocs.io/en/rtfd/installation.html
  [mysql install doc]: https://dev.mysql.com/doc/mysql-installation-excerpt/8.0/en/
  [mysql user doc]: https://dev.mysql.com/doc/refman/5.7/en/adding-users.html
  [mysql schema doc]: https://dev.mysql.com/doc/refman/5.7/en/creating-database.html

