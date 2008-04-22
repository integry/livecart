#!/bin/bash

set -e

PATH=/var/www/livecart
TMP=/tmp/livecart-export
TAR=/var/www/livecart-export.tar
/bin/rm -rf $TMP
/bin/rm -rf $TAR

/usr/bin/rsync -aq --exclude '.hg*' --exclude 'cache' --exclude 'storage' --exclude 'doc' --exclude 'test' --exclude 'build' --exclude 'public/upload' $PATH $TMP

cd $TMP
/bin/tar cf $TAR .
/bin/gzip -9 $TAR

/bin/rm -rf $TMP