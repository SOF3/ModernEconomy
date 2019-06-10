#!/usr/bin/env bash

php=$1
from=$2
to=$3
lib_dir=$4

rm $to

$php -r '$phar = new Phar($argv[2]);
$phar->buildFromDirectory($argv[1]);' $from $to

ls $lib_dir | xargs -I % $php $lib_dir/% $to
