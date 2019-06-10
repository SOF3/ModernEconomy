---
title: Internals/Player Module
---

The player module creates an account for each player so that they can perform basic transactions.

## Account owner types
### `modernecon.player.player`
Accounts owned by this type of account owners represent the assets owned by a player.
A player is identified by its UUID so that it continues to identify despite username changes.

## Account types
### `modernecon.player.cash`
Accounts of this type can be accessible by players anytime.
