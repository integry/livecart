#!/bin/bash

set -e

TMP=/tmp/build
PACKAGE=/var/db/livecart
MAIN=/var/www/livecart
MODULE=$1
SRC=$MAIN/module/$MODULE

rm -rf $TMP
mkdir $TMP
cp -rf $SRC $TMP/$MODULE
find $TMP -name '.git' | xargs rm -rf

cd $TMP

# prepend copyright messages to source files
find -name '*.php' | xargs grep -l Integry | xargs --max-args=1 $MAIN/build/copyrightPhp.sh $MAIN
find -name '*.js' | xargs grep -l Integry | xargs --max-args=1 $MAIN/build/copyrightJs.sh $MAIN

# remove non-distributed files
rm -rf build cache doc update .git* .snap test push status

TAR=$PACKAGE/module/$1.tar
rm -rf $TAR.gz
tar cf $TAR .
gzip -9 $TAR

ZIP=$PACKAGE/module/$1.zip
rm -rf $ZIP
zip -rq $ZIP .